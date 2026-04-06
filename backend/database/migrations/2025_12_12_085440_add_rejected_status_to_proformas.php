<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the 'status' column to include 'rejected'
        DB::statement("ALTER TABLE proformas MODIFY COLUMN status ENUM(
            'pending',
            'opened',
            'closed',
            'published',
            'waiting for approval',
            'waiting for payment',
            'payment collected',
            'completed',
            'rejected'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum without 'rejected'
        DB::statement("ALTER TABLE proformas MODIFY COLUMN status ENUM(
            'pending',
            'opened',
            'closed',
            'published',
            'waiting for approval',
            'waiting for payment',
            'payment collected',
            'completed'
        ) DEFAULT 'pending'");
    }
};
