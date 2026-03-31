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
            $table->boolean('auto_selection_enabled')->default(false)->after('timer_type');
            $table->integer('auto_selection_count')->default(3)->after('auto_selection_enabled');
            $table->enum('auto_selection_criteria', ['lowest_price', 'highest_rating', 'earliest_submission'])->default('lowest_price')->after('auto_selection_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['auto_selection_enabled', 'auto_selection_count', 'auto_selection_criteria']);
        });
    }
};
