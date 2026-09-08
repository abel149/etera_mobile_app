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
        'initial_price'       => 'decimal:2',
        'amount'              => 'decimal:2',
        'amount_is_encrypted' => 'boolean',
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
        return $this->hasMany(ProformaPartPrice::class, 'application_id')->orderBy('id', 'asc');
    }

    public function pdf()
    {
        return $this->hasOne(ApplicationPdf::class, 'application_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'model');
    }

    /**
     * Get the final price (amount is already VAT-inclusive, no discount to apply)
     */
    public function calculateFinalPrice()
    {
        return $this->initial_price ?? $this->amount;
    }

    /**
     * Get the final price (auto-calculated)
     */
    public function getFinalPriceAttribute()
    {
        return $this->calculateFinalPrice();
    }
}
