<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parts_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('proforma_part_id')->index();
            $table->string('image_path', 191); // store image path here
            $table->timestamps();

            // Foreign key (optional, if you want cascade delete)
            $table->foreign('proforma_part_id')
                  ->references('id')
                  ->on('proforma_part')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_images');
    }
};
