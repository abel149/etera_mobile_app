<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['role', 'amount']);

            // Add new columns
            $table->decimal('shopPay', 10, 2)->default(0);
            $table->decimal('garagePay', 10, 2)->default(0);
            $table->decimal('insurancePay', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            $table->dropColumn(['shopPay', 'garagePay', 'insurancePay']);

            $table->string('role')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
        });
    }
};
