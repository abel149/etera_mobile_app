<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('paid_users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('proforma_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('application_id')->nullable()->constrained('proforma_applications')->onDelete('cascade');
        $table->decimal('amount', 10, 2);
        $table->boolean('is_paid')->default(false);
        $table->date('paid_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_users');
    }
};
