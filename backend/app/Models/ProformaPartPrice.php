<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaPartPrice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'part_total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function part()
    {
        return $this->belongsTo(CarPart::class, 'car_part_id');
    }

    public function application()
    {
        return $this->belongsTo(ProformaApplication::class, 'application_id');
    }

    /**
     * Calculate total price for this part
     */
    public function calculateTotalPrice()
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Get the total price (auto-calculated if not set)
     */
    public function getTotalPriceAttribute()
    {
        if ($this->part_total) {
            return $this->part_total;
        }
        return $this->calculateTotalPrice();
    }
}
