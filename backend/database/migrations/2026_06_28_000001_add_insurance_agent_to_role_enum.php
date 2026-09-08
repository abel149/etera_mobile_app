<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix any role values not in the new ENUM before altering
        DB::statement("
            UPDATE users SET role = 'operator'
            WHERE role NOT IN (
                'superadmin','admin','manager','operator','business_owner',
                'insurance','insurance_agent','shop','garage','marketer',
                'individual','accountant'
            )
        ");

        DB::statement("SET SESSION sql_mode = ''");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'superadmin',
                'admin',
                'manager',
                'operator',
                'business_owner',
                'insurance',
                'insurance_agent',
                'shop',
                'garage',
                'marketer',
                'individual',
                'accountant'
            ) DEFAULT 'operator'
        ");
    }

    public function down(): void
    {
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
