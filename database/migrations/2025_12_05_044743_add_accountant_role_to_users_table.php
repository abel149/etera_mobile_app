<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify the enum to include 'accountant'
            $table->enum('role', [
                'admin',
                'business_owner',
                'insurance',
                'shop',
                'garage',
                'employee',
                'marketer',
                'accountant'
            ])->default('employee')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback → remove accountant
            $table->enum('role', [
                'admin',
                'business_owner',
                'insurance',
                'shop',
                'garage',
                'employee',
                'marketer'
            ])->default('employee')->change();
        });
    }
};
