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
        Schema::table('proformas', function (Blueprint $table) {
            $table->boolean('timer_enabled')->default(false);
            $table->integer('timer_duration')->nullable()->comment('Timer duration in minutes');
            $table->timestamp('timer_expires_at')->nullable();
            $table->enum('timer_type', ['fixed', 'unlimited'])->default('fixed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['timer_enabled', 'timer_duration', 'timer_expires_at', 'timer_type']);
        });
    }
};
