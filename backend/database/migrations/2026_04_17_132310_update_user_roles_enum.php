<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
                    public function up()
        {
            DB::statement("
                ALTER TABLE users 
                MODIFY role ENUM(
                    'superadmin','admin','manager',
                    'operator','business_owner',
                    'insurance','shop','garage',
                    'marketer','individual','accountant',
                    'employee',
                    'others'
                ) NOT NULL DEFAULT 'employee'
            ");
        }

        public function down()
        {
            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM(
                    'superadmin','admin','manager',
                    'operator','business_owner','insurance',
                    'shop','garage','marketer','individual','accountant'
                ) NOT NULL DEFAULT 'business_owner'
            ");
        }
        
};
