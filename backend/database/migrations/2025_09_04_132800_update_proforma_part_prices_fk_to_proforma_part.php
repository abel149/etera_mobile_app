<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add proforma_part_id FK to proforma_part_prices so each priced row
     * can be linked directly to the specific proforma part record.
     * Column is nullable so existing rows are unaffected.
     */
    public function up(): void
    {
        Schema::table('proforma_part_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_part_prices', 'proforma_part_id')) {
                $table->unsignedBigInteger('proforma_part_id')
                      ->nullable()
                      ->after('application_id');

                $table->foreign('proforma_part_id')
                      ->references('id')
                      ->on('proforma_part')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_part_prices', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_part_prices', 'proforma_part_id')) {
                $table->dropForeign(['proforma_part_id']);
                $table->dropColumn('proforma_part_id');
            }
        });
    }
};
