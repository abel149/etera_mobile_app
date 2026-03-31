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
    
    protected $casts = [
        'timer_expires_at' => 'datetime',
        'auto_selection_enabled' => 'boolean',
        'auto_selection_count' => 'integer',
        'close_request' => 'boolean',
        'insured' => 'boolean',
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

    public function getRemainingShopsAttribute()
    {
        // If Etera-Chereta mode (required_number_of_shops is 0), return infinity symbol
        if ($this->required_number_of_shops == 0) {
            return '∞';
        }
        return ($this->required_number_of_shops ?? 3) - ($this->numberOfInboxesSentToShops() + $this->applicationsFromShops()->count());
    }

    public function getRemainingGaragesAttribute()
    {
    return ($this->required_number_of_garages ?? 3) - ($this->numberOfInboxesSentToGarages() + $this->applicationsFromGarages()->count());
    }

    public function parts()
    {
        return $this->hasMany(ProformaPart::class)->latest();
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
        return $this->poster->role == 'insurance';
    }

    public function isFromOthers()
    {
        return $this->poster->role != 'insurance';
    }

    public function applications()
    {
        return $this->hasMany(ProformaApplication::class, 'proforma_id')->latest();
    }

    public function canBeAppliedByShop()
    {
        // If Etera-Chereta mode (required_number_of_shops is 0), always allow applications
        if ($this->required_number_of_shops == 0) {
            return true;
        }

        if ($this->isFromInsurance()) {
            return ($this->required_number_of_shops - $this->applications()->where('from', 'shop')->count()) <= 0 ? false : true;
        }

        return ($this->number_of_proformas - $this->applications()->where('from', 'shop')->count()) <= 0 ? false : true;
    }

    public function canBeAppliedByGarage()
    {
        if ($this->isFromInsurance()) {
            return ($this->required_number_of_garages - $this->applications()->where('from', 'garage')->count()) <= 0 ? false : true;
        }

        return false;
    }

    public function isApplicableBy(User $applicant)
    {
        $inboxes = $this->inboxes;

        // For Etera-Chereta mode, check if timer has expired
        if ($this->isEteraCheretaMode() && $this->timer_expires_at && now()->isAfter($this->timer_expires_at)) {
            return false;
        }

        return ($applicant?->role == 'shop' && $this->canBeAppliedByShop()  && ($this->remaining_shops === '∞' || $this->remaining_shops > 0)
        || $applicant?->role == 'garage' && $this->canBeAppliedByGarage()   && $this->remaining_garages > 0
        );
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
                $query->where('role', 'insurance');
            })->when(!empty($brands), function ($q) use ($brands) {
                return $q->whereIn('car_brand_id', $brands);
            });
        }
    
        return $query->whereHas('poster', function ($query) {
            $query->where('role', 'insurance');
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
     * Check if this proforma is in Etera-Chereta mode
     * Etera-Chereta mode is identified by required_number_of_shops being 0
     */
    public function isEteraCheretaMode()
    {
        return $this->required_number_of_shops == 0;
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

        // Determine type
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
        // ===== INSURANCE TYPE =====
        elseif ($type === 'insurance') {
            // Only charge if not insured
            if (!$this->insured) {
                $totalAmount = (float) ($latestCost->insurance_proforma ?? 0);
            }
        }
        // ===== ETERA CHERETA =====
        elseif ($type === 'etera_chereta') {
            $totalAmount = (float) ($latestCost->etera_chereta_cost ?? 0);
        }

        return $totalAmount;
    }
}
