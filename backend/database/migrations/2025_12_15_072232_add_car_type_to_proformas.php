<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->enum('car_type', ['ICE', 'EV', 'Hybrid', 'Others'])
                  ->default('ICE')
                  ->after('status'); // change column position if needed
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn('car_type');
        });
    }
};
