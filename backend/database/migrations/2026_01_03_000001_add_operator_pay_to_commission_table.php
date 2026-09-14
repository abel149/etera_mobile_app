<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            if (!Schema::hasColumn('commission', 'operatorPay')) {
                $table->decimal('operatorPay', 10, 2)->default(0)->after('insurancePay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            if (Schema::hasColumn('commission', 'operatorPay')) {
                $table->dropColumn('operatorPay');
            }
        });
    }
};
