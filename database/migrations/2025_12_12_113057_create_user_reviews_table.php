<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id') // The user being reviewed
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('reviewer_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->tinyInteger('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->text('review')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_reviews');
    }
};
