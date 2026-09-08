<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('partials')) {
            return;
        }

        Schema::create('partials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained('proformas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('inbox_group');
            $table->unsignedSmallInteger('parts_needed')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['proforma_id', 'user_id', 'inbox_group'], 'uq_partial_per_shop_group');
            $table->index(['user_id', 'active']);
            $table->index(['proforma_id', 'inbox_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partials');
    }
};
