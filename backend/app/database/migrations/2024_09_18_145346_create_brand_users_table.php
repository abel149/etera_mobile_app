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
        Schema::create('brand_users', function (Blueprint $table) {
            $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('brand_id');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 

    $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade'); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_users');
    }
};
