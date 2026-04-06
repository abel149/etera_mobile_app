<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class ProformaApplication extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'initial_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function applicationBy()
    {
        return $this->belongsTo(User::class, 'application_by');
    }

    public function prices()
    {
        return $this->hasMany(ProformaPartPrice::class, 'application_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'model');
    }

    /**
     * Calculate final price based on initial price and discount
     */
    public function calculateFinalPrice()
    {
        if ($this->initial_price && $this->discount) {
            $discountAmount = ($this->initial_price * $this->discount) / 100;
            return $this->initial_price - $discountAmount;
        }
        return $this->initial_price ?? $this->amount;
    }

    /**
     * Get the discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        return $this->discount ?? 0;
    }

    /**
     * Get the final price (auto-calculated)
     */
    public function getFinalPriceAttribute()
    {
        return $this->calculateFinalPrice();
    }
}
