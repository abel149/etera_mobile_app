<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Revert the mistaken conversion from migration 2026_06_28_000001:
        // business-owner signups that used the old 'others' value were converted
        // to 'operator' instead of 'business_owner'. Convert them back.
        DB::statement("
            UPDATE users
            SET role = 'business_owner'
            WHERE role = 'operator'
        ");
    }

    public function down(): void
    {
        // Not safely reversible: we cannot distinguish real operators from
        // those that were converted here.
    }
};
