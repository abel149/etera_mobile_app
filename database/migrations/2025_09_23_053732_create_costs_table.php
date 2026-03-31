<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_id')->nullable()->constrained('costs');
            $table->float('proforma_cost');
            $table->float('etera_chereta_cost');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
}
