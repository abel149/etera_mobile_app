<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'call_customer')) {
                $table->boolean('call_customer')->default(false)->after('damage_severity');
            }
        });

        Schema::table('proforma_part', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_part', 'repair_renew')) {
                $table->string('repair_renew')->nullable()->after('component');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (Schema::hasColumn('proformas', 'call_customer')) {
                $table->dropColumn('call_customer');
            }
        });

        Schema::table('proforma_part', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_part', 'repair_renew')) {
                $table->dropColumn('repair_renew');
            }
        });
    }
};
