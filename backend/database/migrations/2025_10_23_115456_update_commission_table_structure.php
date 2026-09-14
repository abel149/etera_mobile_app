<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            if (Schema::hasColumn('commission', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('commission', 'amount')) {
                $table->dropColumn('amount');
            }
            if (!Schema::hasColumn('commission', 'shopPay')) {
                $table->decimal('shopPay', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('commission', 'garagePay')) {
                $table->decimal('garagePay', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('commission', 'insurancePay')) {
                $table->decimal('insurancePay', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            if (Schema::hasColumn('commission', 'shopPay')) {
                $table->dropColumn('shopPay');
            }
            if (Schema::hasColumn('commission', 'garagePay')) {
                $table->dropColumn('garagePay');
            }
            if (Schema::hasColumn('commission', 'insurancePay')) {
                $table->dropColumn('insurancePay');
            }
            if (!Schema::hasColumn('commission', 'role')) {
                $table->string('role')->nullable();
            }
            if (!Schema::hasColumn('commission', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
        });
    }
};
