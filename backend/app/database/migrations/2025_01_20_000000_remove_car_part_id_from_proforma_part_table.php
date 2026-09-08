<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCarPartIdFromProformaPartTable extends Migration
{
    public function up()
    {
        Schema::table('proforma_part', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['car_part_id']);
            
            // Drop the car_part_id column
            $table->dropColumn('car_part_id');
            
            // Add component field if it doesn't exist
            if (!Schema::hasColumn('proforma_part', 'component')) {
                $table->string('component')->nullable();
            }
            
            // Add condition field if it doesn't exist
            if (!Schema::hasColumn('proforma_part', 'condition')) {
                $table->string('condition')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('proforma_part', function (Blueprint $table) {
            // Add back the car_part_id column
            $table->foreignId('car_part_id')->nullable()->constrained('car_parts')->onDelete('cascade');
            
            // Remove the added fields
            $table->dropColumn(['component', 'condition']);
        });
    }
}
