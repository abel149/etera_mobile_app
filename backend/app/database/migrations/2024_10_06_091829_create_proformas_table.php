<?php

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'poster_id');
            $table->string('file_number');
            $table->string('customer_name');
            $table->string('customer_phone_number');
            $table->foreignIdFor(Brand::class, 'car_brand_id');
            $table->string('model');
            $table->string('year');
            $table->integer('required_number_of_shops')->default(3);
            $table->integer('required_number_of_garages')->default(3);
            $table->string('license_plate_number');
            $table->string('chassis_number');
            $table->enum('status', ['pending','opened','closed','published','waiting for approval','waiting for payment','payment collected','completed'])->default('pending');
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proformas');
    }
};
