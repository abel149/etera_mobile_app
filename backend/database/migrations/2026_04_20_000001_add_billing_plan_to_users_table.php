<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'billing_plan')) {
                $table->enum('billing_plan', ['per_invoice', 'monthly', 'weekly'])
                      ->default('per_invoice')
                      ->after('balance');
            }
            if (!Schema::hasColumn('users', 'billing_cycle_start')) {
                $table->date('billing_cycle_start')->nullable()->after('billing_plan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['billing_plan', 'billing_cycle_start'],
                fn($c) => Schema::hasColumn('users', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
