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
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['regular', 'etera_chereta']);
            $table->integer('requested_count');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('hourly_price', 10, 2)->default(0);
            $table->integer('hours')->default(0);
            $table->decimal('vat_rate', 5, 2)->default(15.00);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
