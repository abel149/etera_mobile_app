<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->unsignedTinyInteger('insurance_shop_quota')->nullable()->after('required_number_of_shops');
            $table->unsignedTinyInteger('insurance_garage_quota')->nullable()->after('required_number_of_garages');
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['insurance_shop_quota', 'insurance_garage_quota']);
        });
    }
};
