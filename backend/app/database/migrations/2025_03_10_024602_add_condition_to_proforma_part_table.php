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
        if (!Schema::hasColumn('proforma_part', 'condition')) {
            Schema::table('proforma_part', function (Blueprint $table) {
                $table->string('condition')->default('new');  // Add condition column with a default value
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_part', function (Blueprint $table) {
            $table->dropColumn('condition');  // Remove the condition column if rolling back
        });
    }
};
