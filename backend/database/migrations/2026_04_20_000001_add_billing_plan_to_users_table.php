<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('billing_plan', ['per_invoice', 'monthly', 'weekly'])
                  ->default('per_invoice')
                  ->after('balance');
            $table->date('billing_cycle_start')
                  ->nullable()
                  ->after('billing_plan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['billing_plan', 'billing_cycle_start']);
        });
    }
};
