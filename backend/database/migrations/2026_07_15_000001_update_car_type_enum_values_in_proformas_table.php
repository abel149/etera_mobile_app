<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * RULES & SYNTAX EXPLANATION:
     * - DB::statement() executes raw SQL directly on the database
     * - ALTER TABLE proformas MODIFY COLUMN changes an existing column definition
     * - ENUM('value1', 'value2', ...) defines the allowed values
     * - NOT NULL means the column cannot be empty
     * - DEFAULT 'value' sets the default value for new records
     * 
     * This preserves existing data - only invalid values would cause errors.
     */
    public function up(): void
    {
        // Keep old values and add new ones — no data migration needed
        // Existing records with old values (ICE, EV, Hybrid, Others) stay as-is
        // New records will use the new values shown in the UI
        DB::statement("ALTER TABLE proformas MODIFY COLUMN car_type ENUM('ICE', 'EV', 'Hybrid', 'Others', 'Sedan/S.U.V(GAS)', 'Sedan/S.U.V(EV)', 'Mini Van(GAS)', 'Mini Van(EV)', 'Isuzu/Bus(GAS)', 'Isuzu/Bus(EV)', 'Heavy') NOT NULL DEFAULT 'Sedan/S.U.V(GAS)'");
    }

    /**
     * Reverse the migrations.
     * 
     * This restores the old enum values for rollback.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE proformas MODIFY COLUMN car_type ENUM('ICE', 'EV', 'Hybrid', 'Others') NOT NULL DEFAULT 'ICE'");
    }
};
