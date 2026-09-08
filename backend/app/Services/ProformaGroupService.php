<?php

namespace App\Services;

use App\Models\Inbox;
use App\Models\Partial;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\User;
use App\Notifications\PartialNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProformaGroupService
{
    /**
     * Find the first empty group slot available for a fresh (non-partial) application.
     *
     * "Empty" means priced_count = 0 AND no insurance-inboxed shop is still waiting for it.
     * Protected groups (insurance-inboxed pending) are skipped so public/null-group shops
     * don't collide with dedicated partner slots.
     *
     * Returns null if no fresh empty slot is available.
     */
    public function autoAssignGroup(Proforma $proforma): ?int
    {
        $required = (int) ($proforma->required_number_of_shops ?? 0);

        if ($required <= 0) {
            return null;
        }

        for ($g = 1; $g <= $required; $g++) {
            // Skip if any inboxed SHOP (insurance or admin) is still waiting for this group.
            // Only count shop inboxes — garage inboxes share the same group numbers in
            // standard proformas but must not block shop group assignment.
            $pendingInbox = Inbox::where('proforma_id', $proforma->id)
                ->whereIn('source', ['insurance', 'admin'])
                ->where('inbox_group', $g)
                ->whereHas('user', fn ($q) => $q->where('role', 'shop'))
                ->exists();

            if ($pendingInbox) {
                continue;
            }

            $hasPdfApplication = ProformaApplication::where('proforma_id', $proforma->id)
                ->where('from', 'shop')
                ->where('inbox_group', $g)
                ->whereHas('pdf')
                ->exists();

            if ($hasPdfApplication) {
                continue;
            }

            // Skip if the group already has filled prices (unit_price > 0 or encrypted)
            $pricedCount = ProformaPartPrice::where('proforma_id', $proforma->id)
                ->where('inbox_group', $g)
                ->where(function ($q) {
                    $q->where('unit_price', '>', 0)
                      ->orWhere('price_is_encrypted', true);
                })
                ->count();

            if ($pricedCount > 0) {
                continue;
            }

            return $g;
        }

        return null;
    }

    /**
     * Return all part prices already saved for a given group (for locking UI).
     * Returns a Collection of ProformaPartPrice keyed by car_part_id.
     */
    public function getLockedParts(int $proformaId, int $group): Collection
    {
        // Only return rows with a real price so that legacy zero-price rows
        // are never shown as locked in the UI (price=0 = not actually priced).
        return ProformaPartPrice::with('part')
            ->where('proforma_id', $proformaId)
            ->where('inbox_group', $group)
            ->where(function ($q) {
                $q->where('unit_price', '>', 0)
                  ->orWhere('price_is_encrypted', true);
            })
            ->get()
            ->keyBy('car_part_id');
    }

    /**
     * Post-submission check: if a group was partially filled and has no more inboxed
     * shops waiting, create Partial records for all eligible shops immediately.
     *
     * Called after every shop application submission.
     */
    public function checkAndTriggerPartials(Proforma $proforma, int $group): void
    {
        $totalParts = $proforma->parts()->count();

        if ($totalParts === 0) {
            return;
        }

        // Only count rows where a real price was provided (unit_price > 0 OR encrypted).
        // Zero-price rows must not count — they'd incorrectly mark a partial group as complete.
        $pricedCount = ProformaPartPrice::where('proforma_id', $proforma->id)
            ->where('inbox_group', $group)
            ->where(function ($q) {
                $q->where('unit_price', '>', 0)
                  ->orWhere('price_is_encrypted', true);
            })
            ->count();

        // Condition 1: group was started (at least 1 part genuinely priced)
        if ($pricedCount === 0) {
            return;
        }

        // Condition 2: group is not yet complete
        if ($pricedCount >= $totalParts) {
            return;
        }

        // Condition 3: no inboxed SHOP (insurance or admin) still pending for this group.
        // Only count shop inboxes — in standard proformas, garage inboxes share the same
        // group numbers but must not block partial re-publish for shops.
        $pendingInbox = Inbox::where('proforma_id', $proforma->id)
            ->whereIn('source', ['insurance', 'admin'])
            ->where('inbox_group', $group)
            ->whereHas('user', fn ($q) => $q->where('role', 'shop'))
            ->exists();

        if ($pendingInbox) {
            return;
        }

        // Condition 4: no Partial already exists for this group
        $alreadyBroadcast = Partial::where('proforma_id', $proforma->id)
            ->where('inbox_group', $group)
            ->exists();

        if ($alreadyBroadcast) {
            return;
        }

        $partsNeeded = $totalParts - $pricedCount;

        Log::info("ProformaGroupService: triggering partial broadcast", [
            'proforma_id'  => $proforma->id,
            'group'        => $group,
            'priced'       => $pricedCount,
            'total'        => $totalParts,
            'parts_needed' => $partsNeeded,
        ]);

        $this->broadcastRemainingParts($proforma, $group, $partsNeeded);
    }

    /**
     * Create Partial records for all eligible shops and send PartialNotification.
     *
     * Eligible = role 'shop', NOT already applied to this proforma, NOT currently inboxed.
     */
    public function broadcastRemainingParts(Proforma $proforma, int $group, int $partsNeeded): void
    {
        // Shops that already applied (any group) — used their one submission
        $appliedIds = ProformaApplication::where('proforma_id', $proforma->id)
            ->pluck('application_by');

        // Shops currently inboxed for this proforma — committed to their assigned group
        $inboxedIds = Inbox::where('proforma_id', $proforma->id)
            ->pluck('user_id');

        $excludeIds = $appliedIds->merge($inboxedIds)->unique()->values();

        $eligibleShops = User::where('role', 'shop')
            ->whereNotIn('id', $excludeIds)
            ->when($proforma->car_brand_id, function ($q) use ($proforma) {
                $q->whereHas('brands', fn ($bq) =>
                    $bq->where('brands.id', $proforma->car_brand_id)
                );
            })
            ->get();

        $count = 0;
        foreach ($eligibleShops as $shop) {
            try {
                Partial::firstOrCreate([
                    'proforma_id' => $proforma->id,
                    'user_id'     => $shop->id,
                    'inbox_group' => $group,
                ], [
                    'parts_needed' => $partsNeeded,
                    'active'       => true,
                ]);

                $shop->notify(new PartialNotification($proforma, $group, $partsNeeded));
                $count++;
            } catch (\Throwable $e) {
                Log::warning("ProformaGroupService: failed to create partial for shop {$shop->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("ProformaGroupService: partial broadcast complete", [
            'proforma_id'    => $proforma->id,
            'group'          => $group,
            'parts_needed'   => $partsNeeded,
            'shops_notified' => $count,
        ]);
    }

    /**
     * Find the first group that has been started but is not yet complete.
     * Used as fallback for admin-floated shops when no fully-empty group exists.
     */
    public function findFirstIncompleteGroup(Proforma $proforma): ?int
    {
        $required   = (int) ($proforma->required_number_of_shops ?? 0);
        $totalParts = $proforma->parts()->count();

        if ($required <= 0 || $totalParts === 0) {
            return null;
        }

        for ($g = 1; $g <= $required; $g++) {
            // Don't take over a group that still has an inboxed SHOP (insurance or admin)
            // waiting — that shop is expected to complete the remaining parts themselves.
            // Only count shop inboxes — garage inboxes must not block shop group fallback.
            $pendingInbox = Inbox::where('proforma_id', $proforma->id)
                ->whereIn('source', ['insurance', 'admin'])
                ->where('inbox_group', $g)
                ->whereHas('user', fn ($q) => $q->where('role', 'shop'))
                ->exists();

            if ($pendingInbox) {
                continue;
            }

            $priced = $this->getPricedCount($proforma->id, $g);
            if ($priced > 0 && $priced < $totalParts) {
                return $g;
            }
        }

        return null;
    }

    /**
     * Get how many parts are priced for a given group.
     */
    public function getPricedCount(int $proformaId, int $group): int
    {
        return ProformaPartPrice::where('proforma_id', $proformaId)
            ->where('inbox_group', $group)
            ->where(function ($q) {
                $q->where('unit_price', '>', 0)
                  ->orWhere('price_is_encrypted', true);
            })
            ->count();
    }

    /**
     * Check if a group is fully complete (all parts priced).
     */
    public function isGroupComplete(Proforma $proforma, int $group): bool
    {
        $totalParts = $proforma->parts()->count();
        if ($totalParts === 0) {
            return false;
        }
        return $this->getPricedCount($proforma->id, $group) >= $totalParts;
    }
}
