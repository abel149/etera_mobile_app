<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceCost extends Model
{
    protected $fillable = [
        'user_id',
        'insured_cost',
        'insurance_proforma',
    ];

    protected $casts = [
        'insured_cost'       => 'decimal:2',
        'insurance_proforma' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve the per-proforma cost for an insurance poster.
     *
     * Looks up the custom cost record for the poster's insurance company
     * (handles agents via parent_insurance_id automatically). Falls back to
     * the global Cost record when no custom row exists.
     *
     * @param  User  $poster       The proforma poster (insurance or insurance_agent)
     * @param  Cost  $globalCost   The latest global Cost record (fallback)
     * @param  bool  $insured      Whether the proforma is insured
     * @return float
     */
    public static function resolveForPoster(User $poster, Cost $globalCost, bool $insured): float
    {
        $insuranceUserId = $poster->insuranceId();

        $custom = static::where('user_id', $insuranceUserId)->first();

        if ($insured) {
            return (float) ($custom?->insured_cost ?? $globalCost->insured_cost ?? 0);
        }

        return (float) ($custom?->insurance_proforma ?? $globalCost->insurance_proforma ?? 0);
    }
}
