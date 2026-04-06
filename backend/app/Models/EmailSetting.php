<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EmailSetting extends Model
{
    protected $fillable = ['key', 'enabled', 'description'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Check if a specific email type is enabled.
     * Results are cached for 60 seconds to avoid repeated DB queries.
     */
    public static function isEnabled(string $key): bool
    {
        return Cache::remember("email_setting_{$key}", 60, function () use ($key) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->enabled : true; // Default to enabled if not found
        });
    }

    /**
     * Toggle the enabled status and clear cache.
     */
    public function toggle(): void
    {
        $this->update(['enabled' => !$this->enabled]);
        Cache::forget("email_setting_{$this->key}");
    }
}
