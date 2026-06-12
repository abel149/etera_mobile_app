<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            // Null  = legacy proforma (type determined by shops/garages counts)
            // 'insurance_standard'   = insurance with both shops AND garages (configurable count)
            // 'insurance_shop_only'  = insurance with shops only
            // 'insurance_garage_only'= insurance with garages only
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
