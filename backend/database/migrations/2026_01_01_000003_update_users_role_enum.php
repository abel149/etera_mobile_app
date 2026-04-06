<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert old / invalid roles to operator
        DB::statement("
            UPDATE users
            SET role = 'operator'
            WHERE role NOT IN (
                'superadmin',
                'admin',
                'manager',
                'operator',
                'business_owner',
                'insurance',
                'shop',
                'garage',
                'marketer',
                'individual',
                'accountant'
            )
            OR role IS NULL
            OR role = ''
            OR role = 'employee'
        ");

        // Step 2: Update ENUM (employee removed, manager + operator added)
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'superadmin',
                'admin',
                'manager',
                'operator',
                'business_owner',
                'insurance',
                'shop',
                'garage',
                'marketer',
                'individual',
                'accountant'
            ) DEFAULT 'operator'
        ");
    }
};
