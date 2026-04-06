<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProformaInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'proforma_id',
        'type',
        'requested_count',
        'unit_price',
        'hourly_price',
        'hours',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'is_paid',
        'created_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'hourly_price' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProformaInvoice $invoice) {
            if (empty($invoice->sku)) {
                $invoice->sku = self::generateSku();
            }
        });
    }

    /**
     * Generate a unique 8-character alphanumeric SKU (e.g. A40o90h5)
     */
    public static function generateSku(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        do {
            $sku = '';
            for ($i = 0; $i < 8; $i++) {
                $sku .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSubtotalAttribute()
    {
        if ($this->type === 'etera_chereta') {
            return $this->hourly_price * $this->hours;
        }
        return $this->unit_price * $this->requested_count;
    }
        public function markAsPaid()
    {
        $this->update([
            'is_paid' => true,
        ]);
    }

}
