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
            $table->decimal('operatorPay', 10, 2)->default(0)->after('insurancePay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            $table->dropColumn('operatorPay');
        });
    }
};
