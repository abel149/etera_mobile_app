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
        Schema::table('paid_users', function (Blueprint $table) {
            // Add operator and manager tracking
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null')->comment('Operator who processed the file');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->comment('Manager who reviewed the file');
            
            // Add status tracking for manager review workflow
            $table->enum('status', ['pending_review', 'approved', 'rejected', 'paid'])->default('pending_review')->comment('Payment status in review workflow');
            
            // Add review tracking
            $table->timestamp('reviewed_at')->nullable()->comment('When manager reviewed the file');
            $table->text('rejection_reason')->nullable()->comment('Reason if manager rejected the file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paid_users', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['processed_by', 'reviewed_by', 'status', 'reviewed_at', 'rejection_reason']);
        });
    }
};
