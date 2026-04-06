<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proforma_part_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_part_id');
            $table->unsignedBigInteger('application_id');
            $table->foreign('car_part_id')->references('id')->on('car_parts')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('proforma_applications')->cascadeOnDelete();
                        $table->string('price')->default('0');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_part_prices');
    }
};
