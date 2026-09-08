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
        Schema::table('proforma_applications', function (Blueprint $table) {
            $table->decimal('initial_price', 10, 2)->nullable()->after('amount');
        });

        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('car_part_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_applications', function (Blueprint $table) {
            $table->dropColumn('initial_price');
        });

        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}; 