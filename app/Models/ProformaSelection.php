<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Proforma,
    User,
    ProformaActivityLog
};

class ProformaSelection extends Model
{
    /**
     * Mass assignment
     */
    protected $guarded = [];

    /**
     * Always eager-load operator
     */
    protected $with = ['operator'];

    /**
     * Explicit fillable (optional but safe)
     */
    protected $fillable = [
        'proforma_id',
        'employee_id',
        'active',
        'commission_earned',
        'closed_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * 🔹 FIX: Cast ALL date fields properly
     */
    protected $casts = [
        'active'        => 'boolean',
        'closed_at'     => 'datetime',
        'reviewed_at'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | State Management
    |--------------------------------------------------------------------------
    */

    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    public function activate()
    {
        $this->update(['active' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Commission Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate and record commission earned by operator
     */
    public function calculateCommission(): float
    {
        $operator = $this->operator;

        if (!$operator || !$operator->isOperator()) {
            return 0;
        }

        $commissionAmount = (float) ($operator->commission_per_file ?? 0);

        $this->update([
            'commission_earned' => $commissionAmount,
        ]);

        return $commissionAmount;
    }

    /**
     * Close file and earn commission
     */
    public function closeAndEarnCommission(): float
    {
        $this->update([
            'active'    => false,
            'closed_at' => now(),
        ]);

        return $this->calculateCommission();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePendingReview($query)
    {
        return $query->whereNull('review_status')
                     ->orWhere('review_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Review Actions
    |--------------------------------------------------------------------------
    */

    /**
     * Mark as approved by manager
     */
    public function markAsApproved(int $managerId): void
    {
        // Prevent double approval
        if ($this->review_status === 'approved') {
            return;
        }

        $this->update([
            'review_status' => 'approved',
            'reviewed_by'   => $managerId,
            'reviewed_at'   => now(),
        ]);

    }

    /**
     * Mark as rejected by manager
     */
    public function markAsRejected(int $managerId, ?string $reason = null): void
    {
        $this->update([
            'review_status'     => 'rejected',
            'reviewed_by'       => $managerId,
            'reviewed_at'       => now(),
            'rejection_reason'  => $reason,
        ]);
    }
}
