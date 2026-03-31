<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            $table->decimal('othersPay', 10, 2)->default(0)->after('operatorPay');
        });
    }

    public function down(): void
    {
        Schema::table('commission', function (Blueprint $table) {
            $table->dropColumn('othersPay');
        });
    }
};
