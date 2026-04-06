<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProformaPartTable extends Migration
{
    public function up()
    {
        Schema::create("proforma_part", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("proforma_id")
                ->constrained("proformas")
                ->onDelete("cascade");
            $table
                ->foreignId("car_part_id")
                ->constrained("car_parts")
                ->onDelete("cascade");
            $table->string("number");
            $table->string("grade");
            $table->string("country")->nullable();
            $table->string("quantity")->nullable();
            $table->string("photo")->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("proforma_part");
    }
}
