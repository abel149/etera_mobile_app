<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('proforma_applications')->onDelete('cascade');
            $table->enum('storage_type', ['encrypted', 'plain'])->default('plain');
            $table->mediumText('encrypted_pdf')->nullable();
            $table->text('encrypted_aes_key')->nullable();
            $table->text('aes_iv')->nullable();
            $table->string('original_filename')->default('quotation.pdf');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_pdfs');
    }
};
