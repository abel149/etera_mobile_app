<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Proforma extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $appends = ['number_of_proformas', 'applicants_remaining', 'remaining_garages', 'remaining_shops'];

    protected $guarded = [];

    protected $attributes = [
        'license_plate_number' => '',
    ];

    protected $casts = [
        'timer_expires_at' => 'datetime',
        'auto_selection_enabled' => 'boolean',
        'auto_selection_count' => 'integer',
        'close_request' => 'boolean',
        'insured' => 'boolean',
        'call_customer' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('created_desc', function (Builder $query) {
            $query->orderBy('created_at', 'desc');
        });

        // Send email to 251etera@gmail.com for every new proforma
        static::created(function (Proforma $proforma) {
            // Clear admin dashboard cache so polling picks up new proformas
            \Illuminate\Support\Facades\Cache::forget('admin_proformas_data');

            try {
                $posterName = $proforma->poster?->name ?? 'Unknown';
                $posterRole = $proforma->poster?->role ?? 'Unknown';
                $brandName = $proforma->brand?->name ?? 'N/A';

                // Telegram notification to admins (if configured)
                try {
                    $telegram = new \App\Services\TelegramService();
                    $telegram->sendProformaRequestedNotificationToAdmins($proforma);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send proforma requested Telegram notification to admins', [
                        'proforma_id' => $proforma->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Send email notification for new proforma (if enabled)
                if (\App\Models\EmailSetting::isEnabled('proforma_created')) {
                    \Illuminate\Support\Facades\Mail::raw(
                        "New Proforma Created\n\n" .
                        "File Number: {$proforma->file_number}\n" .
                        "Created By: {$posterName} ({$posterRole})\n" .
                        "Customer: {$proforma->customer_name}\n" .
                        "Phone: {$proforma->customer_phone_number}\n" .
                        "Brand: {$brandName}\n" .
                        "Model: {$proforma->model} ({$proforma->year})\n" .
                        "Type: " . ($proforma->isEteraCheretaMode() ? 'Etera Chereta' : 'Regular') . "\n" .
                        "Created At: " . now()->format('M d, Y h:i A'),
                        function ($message) use ($proforma) {
                            $message->to('251etera@gmail.com')
                                    ->subject("New Proforma #{$proforma->file_number} Created");
                        }
                    );
                }

                \App\Models\SentEmail::log(
                    'proforma_created',
                    '251etera@gmail.com',
                    'ETERA Admin',
                    null,
                    $proforma->id,
                    "New Proforma #{$proforma->file_number} Created",
                    'sent'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send new proforma email to 251etera@gmail.com', [
                    'proforma_id' => $proforma->id,
                    'error' => $e->getMessage(),
                ]);

                try {
                    \App\Models\SentEmail::log(
                        'proforma_created',
                        '251etera@gmail.com',
                        'ETERA Admin',
                        null,
                        $proforma->id,
                        "New Proforma #{$proforma->file_number} Created",
                        'failed',
                        $e->getMessage()
                    );
                } catch (\Throwable $logEx) {
                    // Silently fail - don't break proforma creation
                }
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'car_brand_id');
    }

    public function getNumberOfProformasAttribute()
    {
        return $this->required_number_of_shops + $this->required_number_of_garages;
    }

    public function getApplicantsRemainingAttribute()
    {
        return $this->required_number_of_shops + $this->required_number_of_garages;
    }

    public function isGarageOnlyInsurance(): bool
    {
        return $this->proforma_type === 'insurance_garage_only';
    }

    public function isShopOnlyInsurance(): bool
    {
        return $this->proforma_type === 'insurance_shop_only';
    }

    public function isShopGarageInsurance(): bool
    {
        return $this->proforma_type === 'insurance_shop_garage';
    }

    public function getRemainingShopsAttribute()
    {
        if ($this->isGarageOnlyInsurance()) {
            return 0;
        }
        if ($this->isEteraCheretaMode()) {
            return '∞';
        }
        return max(0, $this->required_number_of_shops - $this->applicationsFromShops()->count());
    }

    public function getRemainingGaragesAttribute()
    {
        if ($this->isShopOnlyInsurance()) {
            return 0;
        }
        return max(0, $this->required_number_of_garages - $this->applicationsFromGarages()->count());
    }

    // ── Insurance partner quota ─────────────────────────────────────────────────
    // Returns the number of required-slots insurance has reserved via partner inboxes.
    // Admin float slots = required_total - insurance_quota - admin_inboxed.
    // Null (proformas created before this feature) = 0 (no insurance reservation).

    public function shopPartnerQuota(): int
    {
        return (int) ($this->insurance_shop_quota ?? 0);
    }

    public function garagePartnerQuota(): int
    {
        return (int) ($this->insurance_garage_quota ?? 0);
    }

    public function hasPartnerShopApplied(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('proforma_applications', 'application_source')) {
            return false;
        }
        return $this->applications()->where('from', 'shop')->where('application_source', 'partner')->exists();
    }

    public function hasPartnerGarageApplied(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('proforma_applications', 'application_source')) {
            return false;
        }
        return $this->applications()->where('from', 'garage')->where('application_source', 'partner')->exists();
    }

    // ── Admin inbox quota (each admin inbox entry = 1 dedicated slot) ──

    public function adminShopInboxCount(): int
    {
        return $this->inboxes()
            ->where('source', 'admin')
            ->whereHas('user', fn($q) => $q->where('role', 'shop'))
            ->count();
    }

    public function adminGarageInboxCount(): int
    {
        return $this->inboxes()
            ->where('source', 'admin')
            ->whereHas('user', fn($q) => $q->where('role', 'garage'))
            ->count();
    }

    public function adminShopApplicationsCount(): int
    {
        return $this->applications()->where('from', 'shop')->where('application_source', 'admin')->count();
    }

    public function adminGarageApplicationsCount(): int
    {
        return $this->applications()->where('from', 'garage')->where('application_source', 'admin')->count();
    }

    // ── Float (public) quota ──
    // = required - partnerQuota - (adminInboxRemaining + adminApplied)

    public function floatShopQuota(): int
    {
        return max(0, $this->required_number_of_shops
            - $this->shopPartnerQuota()
            - $this->adminShopApplicationsCount()
            - $this->adminShopInboxCount());
    }

    public function floatGarageQuota(): int
    {
        return max(0, $this->required_number_of_garages
            - $this->garagePartnerQuota()
            - $this->adminGarageApplicationsCount()
            - $this->adminGarageInboxCount());
    }

    public function publicShopApplicationsCount(): int
    {
        return $this->applications()->where('from', 'shop')->where('application_source', 'public')->count();
    }

    public function publicGarageApplicationsCount(): int
    {
        return $this->applications()->where('from', 'garage')->where('application_source', 'public')->count();
    }

    public function parts()
    {
        return $this->hasMany(ProformaPart::class)->latest();
    }

    /**
     * Calculate parts pricing progress across all shop applications.
     * Returns ['filled' => int, 'total' => int].
     *
     * Example: 3 required shops × 10 parts = 30 total slots.
     * If 2 shops fully filled (20) + 1 partial with 5 = 25/30.
     */
    public function partsPricingProgress(): array
    {
        if ($this->isGarageOnlyInsurance()) {
            return ['filled' => 0, 'total' => 0];
        }

        $totalParts = $this->parts()->count();
        if ($totalParts === 0) {
            return ['filled' => 0, 'total' => 0];
        }

        $requiredShops   = (int) ($this->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($this->required_number_of_garages ?? 0);
        $isEteraChereta  = ($requiredShops + $requiredGarages) === 0;

        if ($isEteraChereta) {
            $shopAppCount   = $this->applications()->where('from', 'shop')->count();
            $totalSlots     = $shopAppCount * $totalParts;
        } else {
            $totalSlots     = $totalParts * max(1, $requiredShops);
        }

        $filledParts = (int) $this->applications()
            ->where('from', 'shop')
            ->sum('filled_parts_count');

        return ['filled' => $filledParts, 'total' => (int) $totalSlots];
    }

    // Removed broken insurance() relationship - insurance_id column doesn't exist
    // Use poster() relationship instead, and check poster->role == 'insurance'

    public function poster()
    {
        return $this->belongsTo(User::class, 'poster_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function images()
    {
        return $this->hasMany(Image::class)->latest();
    }

    public function videos()
    {
        return $this->hasMany(Video::class)->latest();
    }

    public function audios()
    {
        return $this->hasMany(Audio::class)->latest();
    }

    public function allowedApplicants()
    {
        return $this->hasMany(AllowedApplicants::class, 'proforma_id');
    }

    public function isFromInsurance()
    {
        return in_array($this->poster->role, ['insurance', 'insurance_agent']);
    }

    public function isFromOthers()
    {
        return !in_array($this->poster->role, ['insurance', 'insurance_agent']);
    }

    public function applications()
    {
        return $this->hasMany(ProformaApplication::class, 'proforma_id')->latest();
    }

    public function canBeAppliedByShop()
    {
        if ($this->isGarageOnlyInsurance()) {
            return false;
        }
        if ($this->isEteraCheretaMode()) {
            return true;
        }
        if ($this->required_number_of_shops == 0) {
            return false;
        }
        if ($this->isShopGarageInsurance()) {
            return $this->applications()->where('from', 'shop')->count() < $this->required_number_of_shops;
        }
        if ($this->isFromInsurance()) {
            // For group-based proformas: a slot is open when fewer groups are fully priced
            // than required. Using application count is wrong because partial fills produce
            // extra applications without claiming new groups.
            $totalParts = $this->parts()->count();
            if ($totalParts > 0) {
                $completePriceGroups = \App\Models\ProformaPartPrice::where('proforma_id', $this->id)
                    ->whereNotNull('inbox_group')
                    ->select('inbox_group')
                    ->groupBy('inbox_group')
                    ->havingRaw('COUNT(DISTINCT car_part_id) >= ?', [$totalParts])
                    ->pluck('inbox_group');
                $pdfGroups = $this->applications()
                    ->where('from', 'shop')
                    ->whereNotNull('inbox_group')
                    ->whereHas('pdf')
                    ->pluck('inbox_group');
                $completeGroups = $completePriceGroups->merge($pdfGroups)->unique()->count();
                return $completeGroups < $this->required_number_of_shops;
            }
            return ($this->required_number_of_shops - $this->applications()->where('from', 'shop')->count()) > 0;
        }
        return ($this->number_of_proformas - $this->applications()->where('from', 'shop')->count()) > 0;
    }

    public function canBeAppliedByGarage()
    {
        if ($this->isShopOnlyInsurance()) {
            return false;
        }
        if ($this->isGarageOnlyInsurance() || $this->isFromInsurance()) {
            return ($this->required_number_of_garages - $this->applications()->where('from', 'garage')->count()) > 0;
        }
        return false;
    }

    public function isApplicableBy(User $applicant)
    {
        // Etera-Chereta timer expiry check
        if ($this->isEteraCheretaMode() && $this->timer_expires_at && now()->isAfter($this->timer_expires_at)) {
            return false;
        }

        // Etera-Chereta: unlimited applicants
        if ($this->isEteraCheretaMode()) {
            return ($applicant->role === 'shop' && $this->canBeAppliedByShop())
                || ($applicant->role === 'garage' && $this->canBeAppliedByGarage());
        }

        $isDualGarageAsShop = $this->isShopGarageInsurance()
            && $applicant->role === 'garage'
            && $applicant->shop_garage == 1;

        if ($applicant->role === 'shop' || $isDualGarageAsShop) {
            if ($this->isShopGarageInsurance() && $applicant->shop_garage != 1) return false;
            if (!$this->canBeAppliedByShop()) return false;

            $isInsuranceInboxed = $this->inboxes()
                ->where('user_id', $applicant->id)->where('source', 'insurance')->exists();
            $isAdminInboxed = !$isInsuranceInboxed && $this->inboxes()
                ->where('user_id', $applicant->id)->where('source', 'admin')->exists();

            if ($isInsuranceInboxed) {
                // Allow up to insurance_shop_quota partners to apply before slot closes
                if (!\Illuminate\Support\Facades\Schema::hasColumn('proforma_applications', 'application_source')) {
                    return !$this->hasPartnerShopApplied();
                }
                $appliedCount = $this->applications()
                    ->where('from', 'shop')->where('application_source', 'partner')->count();
                $quota = (int) ($this->insurance_shop_quota ?? 1);
                return $appliedCount < $quota;
            } elseif ($isAdminInboxed) {
                // Admin-designated slot: dedicated to this specific user
                return true;
            } else {
                // Public float slot: open if there is still an empty group to claim
                $groupService = new \App\Services\ProformaGroupService();
                return $groupService->autoAssignGroup($this) !== null;
            }
        }

        if ($applicant->role === 'garage') {
            if (!$this->canBeAppliedByGarage()) return false;

            $isInsuranceInboxed = $this->inboxes()
                ->where('user_id', $applicant->id)->where('source', 'insurance')->exists();
            $isAdminInboxed = !$isInsuranceInboxed && $this->inboxes()
                ->where('user_id', $applicant->id)->where('source', 'admin')->exists();

            if ($isInsuranceInboxed) {
                // Allow up to insurance_garage_quota partners to apply before slot closes
                if (!\Illuminate\Support\Facades\Schema::hasColumn('proforma_applications', 'application_source')) {
                    return !$this->hasPartnerGarageApplied();
                }
                $appliedCount = $this->applications()
                    ->where('from', 'garage')->where('application_source', 'partner')->count();
                $quota = (int) ($this->insurance_garage_quota ?? 1);
                return $appliedCount < $quota;
            } elseif ($isAdminInboxed) {
                return true;
            } else {
                return ($this->required_number_of_garages - $this->applications()->where('from', 'garage')->count()) > 0;
            }
        }

        return false;
    }

    public function numberOfInboxesSentToShops()
    {
        $count = 0;
        $this->inboxes?->each(function ($inbox) use (&$count) {
            if ($inbox->user?->role == 'shop') {
                $count++;
            }
        });

        return $count;
    }

    public function numberOfInboxesSentToGarages()
    {
        $count = 0;
        $this->inboxes?->each(function ($inbox) use (&$count) {
            if ($inbox->user?->role == 'garage') {
                $count++;
            }
        });

        return $count;
    }

    public function applicationsFromShops()
    {
        return $this->applications()->where('from', 'shop');
    }

    public function applicationsFromGarages()
    {
        return $this->applications()->where('from', 'garage');
    }

    public function userAlreadyApplied($userId)
    {
        return $this->applications()->where('application_by', $userId)->exists();
    }

    public function scopeFromInsurances($query)
    {
        if (auth()->check() && auth()->user()->role === 'shop') {
            $brands = auth()->user()->brands()->pluck('brand_id')->toArray();
    
            return $query->whereHas('poster', function ($query) {
                $query->whereIn('role', ['insurance', 'insurance_agent']);
            })->when(!empty($brands), function ($q) use ($brands) {
                return $q->whereIn('car_brand_id', $brands);
            });
        }
    
        return $query->whereHas('poster', function ($query) {
            $query->whereIn('role', ['insurance', 'insurance_agent']);
        });
    }
    

    public function scopeFromOthers($query)
    {
        $brands = [];
        if (auth()->check() && auth()->user()->role === 'shop') {
            $brands = auth()->user()->brands()->pluck('brand_id');

            return $query->whereHas('poster', function ($query) {
                $query->whereIn('role', ['business_owner', 'garage', 'others']);
            })->whereIn('car_brand_id', $brands);
        }

        return $query->whereHas('poster', function ($query) {
            $query->whereIn('role', ['business_owner', 'garage', 'others']);
        });
    }

    public function scopeNotTaken($query)
    {
    }

    public function selected()
    {
        return ProformaSelection::where('proforma_id', $this->id)->where('active', true)->exists();
    }

    public function selectedBy()
{
    return $this->hasOne(ProformaSelection::class)->where('active', true);
}


    public function verify()
    {
        $this->update(['status' => 'completed']);
        $this->update(['verified' => true]);
    }

    public function isSentTo(User $user)
    {
        return $this->inboxes()->where('user_id', $user->id)->exists();
    }

    public function isTimerExpired()
    {
        // Check if this is Etera-Chereta mode (required_number_of_shops = 0)
        if (!$this->isEteraCheretaMode()) {
            return false;
        }
        return $this->timer_expires_at && $this->timer_expires_at->isPast();
    }

    public function getRemainingTime()
    {
        if (!$this->isEteraCheretaMode()) {
            return null;
        }
        
        if (!$this->timer_expires_at) {
            return null;
        }
        
        $now = now();
        if ($this->timer_expires_at->isPast()) {
            return 0;
        }
        
        return $now->diffInSeconds($this->timer_expires_at, false);
    }

    public function inboxes()
    {
        return $this->hasMany(Inbox::class);
    }

    public function partials()
    {
        return $this->hasMany(\App\Models\Partial::class);
    }

    public function selections()
    {
        return $this->hasMany(ProformaSelection::class, 'proforma_id');
    }

    // New relationship for ProformaInvoice
    public function proformaInvoice()
    {
        return $this->hasOne(ProformaInvoice::class);
    }
    
    public function scheduleAutoSelection()
    {
        if ($this->isEteraCheretaMode() && $this->timer_expires_at) {
            $delay = $this->timer_expires_at->diffInSeconds(now());
            if ($delay > 0) {
                \App\Jobs\AutoSelectProformaOffers::dispatch($this->id)->delay(now()->addSeconds($delay));
            }
        }
    }

    public function getFormattedRemainingTime()
    {
        if (!$this->isEteraCheretaMode()) {
            return 'Not applicable';
        }
        if (!$this->timer_expires_at) {
            return 'Not set';
        }
        if ($this->timer_expires_at->isPast()) {
            return 'Expired';
        }
        $remaining = $this->timer_expires_at->diffInSeconds(now());
        $hours = floor($remaining / 3600);
        $minutes = floor(($remaining % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Check if this proforma is in Etera-Chereta mode.
     * Etera-Chereta requires BOTH shops AND garages to be 0.
     * Garage-only insurance (shops=0, garages>0) is NOT Etera-Chereta.
     */
    public function isEteraCheretaMode()
    {
        return $this->required_number_of_shops == 0 && $this->required_number_of_garages == 0;
    }

    public function activityLogs()
    {
        return $this->hasMany(ProformaActivityLog::class)->latest();
    }

    /**
     * Calculate proforma price using the same logic as /verify route
     * Returns the total amount that would be charged
     */
    public function calculatePrice()
    {
        $latestCost = \App\Models\Cost::orderBy('created_at', 'desc')->first();
        if (!$latestCost) {
            return 0;
        }

        $vatRate = 0.15;
        $requiredShops = (int) ($this->required_number_of_shops ?? 0);
        $requiredGarages = (int) ($this->required_number_of_garages ?? 0);

        // Explicit insurance subtypes always use insurance billing
        if ($this->proforma_type && str_starts_with($this->proforma_type, 'insurance_')) {
            if ($this->insured) {
                return 0;
            }

            $perProformaCost = (float) ($latestCost->insurance_proforma ?? 0);
            if ($perProformaCost <= 0) {
                return 0;
            }

            $filledGroups = \App\Models\ProformaApplication::where('proforma_id', $this->id)
                ->whereNotNull('inbox_group')
                ->distinct()
                ->pluck('inbox_group')
                ->count();
            if ($filledGroups <= 0) {
                $filledGroups = \App\Models\ProformaApplication::where('proforma_id', $this->id)->count();
            }

            $totalParts = $this->parts()->count();
            $isGarageOnly = $this->isGarageOnlyInsurance();
            $isDualService = $this->isShopGarageInsurance();

            if ($isGarageOnly) {
                return round($perProformaCost * $filledGroups, 2);
            } elseif ($isDualService && $totalParts > 0) {
                $partsFilled = (int) \App\Models\ProformaApplication::where('proforma_id', $this->id)
                    ->where('from', 'shop')
                    ->sum('filled_parts_count');

                $shopPortion = $perProformaCost * ($partsFilled / $totalParts);

                // Garage is charged per filled group (each group includes a garage application)
                $garagePortion = $perProformaCost * $filledGroups;

                return round($shopPortion + $garagePortion, 2);
            } elseif ($totalParts > 0) {
                $partsFilled = (int) \App\Models\ProformaApplication::where('proforma_id', $this->id)
                    ->where('from', 'shop')
                    ->sum('filled_parts_count');

                return round($perProformaCost * ($partsFilled / $totalParts), 2);
            }

            return round($perProformaCost * $filledGroups, 2);
        }

        // Legacy type determination by counts
        if ($requiredShops > 0 && $requiredGarages == 0) {
            $type = 'regular';
        } elseif ($requiredShops == 3 && $requiredGarages == 3) {
            $type = 'insurance';
        } elseif (($requiredShops + $requiredGarages) == 0) {
            $type = 'etera_chereta';
        } else {
            $type = 'unknown';
        }

        $totalAmount = 0;

        // ===== REGULAR TYPE =====
        if ($type === 'regular') {
            $filledApplications = \App\Models\ProformaApplication::where('proforma_id', $this->id)->get();
            $count = $filledApplications->count();
            if ($count > 0) {
                $unitField = "{$count}_proforma_cost";
                $totalAmount = (float) ($latestCost->$unitField ?? 0);
            }
        }
        // ===== INSURANCE TYPE (legacy 3+3) =====
        elseif ($type === 'insurance') {
            if (!$this->insured) {
                $perProformaCost = (float) ($latestCost->insurance_proforma ?? 0);
                $totalParts = $this->parts()->count();

                $filledGroups = \App\Models\ProformaApplication::where('proforma_id', $this->id)
                    ->whereNotNull('inbox_group')
                    ->distinct()
                    ->pluck('inbox_group')
                    ->count();
                if ($filledGroups <= 0) {
                    $filledGroups = \App\Models\ProformaApplication::where('proforma_id', $this->id)->count();
                }

                if ($totalParts > 0 && $perProformaCost > 0) {
                    $partsFilled = (int) \App\Models\ProformaApplication::where('proforma_id', $this->id)
                        ->where('from', 'shop')
                        ->sum('filled_parts_count');

                    $shopPortion = $perProformaCost * ($partsFilled / $totalParts);

                    // Garage is charged per filled group (each group includes a garage application)
                    $garagePortion = $perProformaCost * $filledGroups;
                    $totalAmount = round($shopPortion + $garagePortion, 2);
                } else {
                    $totalAmount = $perProformaCost * $filledGroups;
                }
            }
        }
        // ===== ETERA CHERETA =====
        elseif ($type === 'etera_chereta') {
            $totalAmount = (float) ($latestCost->etera_chereta_cost ?? 0);
        }

        return $totalAmount;
    }
}
