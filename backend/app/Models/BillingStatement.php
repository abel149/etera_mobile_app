<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'owner_id',
        'period_type',
        'period_start',
        'period_end',
        'proforma_count',
        'subtotal',
        'vat_amount',
        'total_amount',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'chapa_checkout_url',
    ];

    protected $casts = [
        'period_start'    => 'date',
        'period_end'      => 'date',
        'subtotal'        => 'decimal:2',
        'vat_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'proforma_count'  => 'integer',
        'paid_at'         => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BillingStatement $statement) {
            if (empty($statement->sku)) {
                $statement->sku = self::generateSku();
            }
        });
    }

    public static function generateSku(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $sku = 'BS-';
            for ($i = 0; $i < 9; $i++) {
                $sku .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    // =====================
    // Relationships
    // =====================

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The proforma invoices that belong to this billing period.
     * Resolved dynamically by owner + date range.
     */
    public function proformaInvoices()
    {
        return ProformaInvoice::whereHas('proforma', function ($q) {
            $q->where('poster_id', $this->owner_id)
              ->whereBetween('created_at', [
                  $this->period_start->startOfDay(),
                  $this->period_end->endOfDay(),
              ]);
        })->get();
    }

    // =====================
    // Helpers
    // =====================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsPaid(string $method = null, string $reference = null): void
    {
        $this->update([
            'status'            => 'paid',
            'paid_at'           => now(),
            'payment_method'    => $method,
            'payment_reference' => $reference,
        ]);
    }
}
