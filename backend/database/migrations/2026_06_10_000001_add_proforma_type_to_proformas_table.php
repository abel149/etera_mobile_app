<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            // null                  = legacy (type resolved by shop/garage counts)
            // 'insurance_standard'  = insurance with both shops + garages (always 3+3)
            // 'insurance_shop_only' = insurance sending to shops only (configurable count)
            // 'insurance_garage_only' = insurance sending to garages only (configurable count)
            $table->string('proforma_type', 30)->nullable()->after('required_number_of_garages');
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn('proforma_type');
        });
    }
};
