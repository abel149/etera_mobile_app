<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->boolean('call_customer')->default(false)->after('damage_severity');
        });

        Schema::table('proforma_part', function (Blueprint $table) {
            $table->string('repair_renew')->nullable()->after('component');
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn('call_customer');
        });

        Schema::table('proforma_part', function (Blueprint $table) {
            $table->dropColumn('repair_renew');
        });
    }
};
