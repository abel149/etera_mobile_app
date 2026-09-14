<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'car_type')) {
                $table->enum('car_type', ['ICE', 'EV', 'Hybrid', 'Others'])
                      ->default('ICE')
                      ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (Schema::hasColumn('proformas', 'car_type')) {
                $table->dropColumn('car_type');
            }
        });
    }
};
