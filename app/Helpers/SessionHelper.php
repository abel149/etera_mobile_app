<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class SessionHelper
{
    /**
     * Check if user already has an active Laravel session.
     * Laravel stores sessions in:
     * storage/framework/sessions/*.txt
     */
    public static function hasActiveSession($userId): bool
    {
        $sessionPath = storage_path('framework/sessions');

        if (!File::exists($sessionPath)) {
            return false;
        }

        // Loop through all session files
        foreach (File::files($sessionPath) as $file) {
            $content = File::get($file->getRealPath());

            // Look for the stored user ID inside the session payload
            if (str_contains($content, "\"user_id\";i:$userId;")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete existing sessions for user (cleanup).
     */
    public static function deleteUserSessions($userId): void
    {
        $sessionPath = storage_path('framework/sessions');

        if (!File::exists($sessionPath)) {
            return;
        }

        foreach (File::files($sessionPath) as $file) {
            $content = File::get($file->getRealPath());

            if (str_contains($content, "\"user_id\";i:$userId;")) {
                File::delete($file->getRealPath());
            }
        }
    }
}
