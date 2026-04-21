<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_statements', function (Blueprint $table) {
            $table->id();

            $table->string('sku', 12)->unique();

            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            $table->enum('period_type', ['monthly', 'weekly']);
            $table->date('period_start');
            $table->date('period_end');

            $table->unsignedInteger('proforma_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'sent', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            // Chapa payment integration (future)
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('chapa_checkout_url')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'period_start', 'period_end']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_statements');
    }
};
