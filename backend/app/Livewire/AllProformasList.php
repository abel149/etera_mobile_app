<?php

namespace App\Livewire;

use App\Models\Partial;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AllProformasList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filters = [
        'license'   => '',
        'type'      => 'default',
        'component' => 'Both',
        'car_type'  => 'All',
        'grade'     => 'All',
    ];

    public $sortBy = 'desc';

    public function updating($name, $value)
    {
        if (str_starts_with($name, 'filters.')) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->filters = [
            'license'   => '',
            'type'      => 'default',
            'component' => 'Both',
            'car_type'  => 'All',
            'grade'     => 'All',
        ];

        $this->resetPage();
    }

    public function render()
    {
        $user   = Auth::user();
        $userId = $user->id;

        /**
         * Pre-fetch Partial proforma IDs for this user before the base query.
         * Proformas with active Partial records may have status='pending' (closed
         * after all required shops applied) but still need partial parts filled.
         */
        $partialProformaIds = Partial::where('user_id', $userId)
            ->where('active', true)
            ->pluck('proforma_id')
            ->toArray();

        /**
         * Base Query
         */
        $query = Proforma::query()
            ->when($user->shop_garage != 1, fn ($q) =>
                $q->where(fn ($typeQ) =>
                    $typeQ->whereNull('proforma_type')
                        ->orWhere('proforma_type', '!=', 'insurance_shop_garage')
                )
            )
            ->where(function ($q) use ($partialProformaIds) {
                // Normal proformas: must be published
                $q->where('status', 'published')
                // Partial proformas: also include pending ones the user was invited to fill
                  ->orWhere(function ($pq) use ($partialProformaIds) {
                      $pq->where('status', 'pending')
                         ->whereIn('id', $partialProformaIds);
                  });
            })
            ->where(function ($q) {
                $q->whereNull('proforma_type')
                  ->orWhere('proforma_type', '!=', 'insurance_garage_only');
            });

        /**
         * ✅ Brand filter — ONLY brands accepted by logged-in user
         */
        /**
 * Brand filter — uses car_brand_id (correct column)
 */
$acceptedBrandIds = $user->brands()->pluck('brands.id')->toArray();

if (!empty($acceptedBrandIds)) {
    $query->whereIn('car_brand_id', $acceptedBrandIds);
} else {
    // No brands assigned → return empty result
    $query->whereRaw('1 = 0');
}


        /**
         * Exclude already applied proformas
         */
        $appliedProformaIds = ProformaApplication::where('application_by', $userId)
            ->pluck('proforma_id')
            ->toArray();

        if (!empty($appliedProformaIds)) {
            // Exclude applied proformas, UNLESS the user still has an active Partial
            // record for a different group of the same proforma.
            $query->where(function ($q) use ($appliedProformaIds, $userId) {
                $q->whereNotIn('id', $appliedProformaIds)
                  ->orWhereHas('partials', fn ($pq) =>
                      $pq->where('user_id', $userId)->where('active', true)
                  );
            });
        }

        /**
         * Slot availability: show fresh proformas only when empty slots exist,
         * OR show if this user has an active Partial record for the proforma.
         */
        $query->where(function ($q) use ($userId) {
            $q->where(function ($freshQ) {
                // Non-insurance proformas (no required groups) are always visible
                $freshQ->where(function ($inner) {
                    $inner->where('required_number_of_shops', 0)
                          ->orWhereNull('required_number_of_shops');
                })
                // Insurance proformas: visible only if at least one empty group remains
                ->orWhere(function ($inner) {
                    $inner->where('required_number_of_shops', '>', 0)
                          ->whereRaw(
                              '(SELECT COUNT(DISTINCT inbox_group)
                                FROM proforma_part_prices
                                WHERE proforma_id = proformas.id
                                AND inbox_group IS NOT NULL)
                               < proformas.required_number_of_shops'
                          );
                });
            })
            // Always show if this user is still inboxed (covers partial-fill flow where
            // the shop needs to fill remaining parts in their assigned group)
            ->orWhereHas('inboxes', fn ($iq) =>
                $iq->where('user_id', $userId)
            )
            // Always show if this user has an active Partial broadcast record
            ->orWhereHas('partials', fn ($pq) =>
                $pq->where('user_id', $userId)->where('active', true)
            );
        });

        /**
         * Filter: Poster Type
         */
        switch ($this->filters['type']) {
            case 'insurance':
                $query->whereHas('poster', fn ($q) =>
                    $q->where('role', 'insurance')
                );
                break;

            case 'others':
                $query->whereHas('poster', fn ($q) =>
                    $q->whereIn('role', ['business_owner', 'garage', 'individual'])
                );
                break;
        }

        /**
         * Filter: License Plate OR Phone
         */
        if (!empty($this->filters['license'])) {
            $search = trim($this->filters['license']);

            $query->where(function ($q) use ($search) {
                $q->where('license_plate_number', 'like', "%{$search}%")
                  ->orWhereHas('poster', fn ($q2) =>
                      $q2->where('customer_phone_number', 'like', "%{$search}%")
                  );
            });
        }

        /**
         * Filter: Component
         */
        if ($this->filters['component'] !== 'Both') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('component', $this->filters['component']);
            });
        }

        /**
         * Filter: Car Type
         */
        if ($this->filters['car_type'] !== 'All') {
            $query->where('car_type', $this->filters['car_type']);
        }

        /**
         * Filter: Grade (partial match from proforma_part)
         */
        if ($this->filters['grade'] !== 'All') {
            $query->whereIn('id', function ($sub) {
                $sub->select('proforma_id')
                    ->from('proforma_part')
                    ->where('grade', 'LIKE', '%' . $this->filters['grade'] . '%');
            });
        }

        /**
         * Sort & Paginate
         */
        $proformas = $query
            ->orderBy('created_at', $this->sortBy)
            ->paginate(10);

        // Fetch active Partial records for this user, grouped by proforma_id
        // (Multiple Partials may exist for different groups of the same proforma)
        $partialsByProformaId = Partial::where('user_id', $userId)
            ->where('active', true)
            ->whereIn('proforma_id', $proformas->pluck('id'))
            ->get()
            ->groupBy('proforma_id');

        return view('livewire.all-proformas-list', [
            'proformas'            => $proformas,
            'components'           => ['Both', 'Body Parts', 'Mechanical Parts'],
            'partialsByProformaId' => $partialsByProformaId,
        ]);
    }
}
