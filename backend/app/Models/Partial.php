<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partial extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active'       => 'boolean',
        'inbox_group'  => 'integer',
        'parts_needed' => 'integer',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: only active partial requests.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Deactivate all partial records for a given proforma + group.
     * Called when the group becomes fully priced.
     */
    public static function deactivateGroup(int $proformaId, int $group): void
    {
        static::where('proforma_id', $proformaId)
            ->where('inbox_group', $group)
            ->update(['active' => false]);
    }

    /**
     * Remove all partial records for a given user + proforma (one-submission rule).
     * Called after a shop submits to any group of this proforma.
     */
    public static function clearForUser(int $proformaId, int $userId): void
    {
        static::where('proforma_id', $proformaId)
            ->where('user_id', $userId)
            ->delete();
    }
}
