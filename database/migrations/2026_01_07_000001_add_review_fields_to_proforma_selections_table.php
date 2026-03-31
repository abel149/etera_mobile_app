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
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->nullable()->after('closed_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('review_status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);
        });
    }
};

