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
        Schema::table('proforma_selections', function (Blueprint $table) {
            // Add commission tracking for operators
            $table->decimal('commission_earned', 10, 2)->default(0)->comment('Commission amount earned by operator for this file');
            $table->timestamp('closed_at')->nullable()->comment('When the operator closed/completed this file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            $table->dropColumn(['commission_earned', 'closed_at']);
        });
    }
};
