<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'proforma_id',
        'application_id',
        'amount',
        'is_paid',
        'paid_at',
        'status',
        'processed_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // =====================
    // Relationships
    // =====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function application()
    {
        return $this->belongsTo(ProformaApplication::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // =====================
    // Scopes
    // =====================

    public function scopePending($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // =====================
    // Status Actions
    // =====================

    public function markAsPaid()
    {
        $this->update([
            'is_paid' => true,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsApproved($managerId)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $managerId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function rejectWithReason($managerId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $managerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
