<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paid_users', function (Blueprint $table) {
            if (!Schema::hasColumn('paid_users', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()
                      ->constrained('users')->onDelete('set null')
                      ->comment('Operator who processed the file');
            }
            if (!Schema::hasColumn('paid_users', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()
                      ->constrained('users')->onDelete('set null')
                      ->comment('Manager who reviewed the file');
            }
            if (!Schema::hasColumn('paid_users', 'status')) {
                $table->enum('status', ['pending_review', 'approved', 'rejected', 'paid'])
                      ->default('pending_review')
                      ->comment('Payment status in review workflow');
            }
            if (!Schema::hasColumn('paid_users', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()
                      ->comment('When manager reviewed the file');
            }
            if (!Schema::hasColumn('paid_users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()
                      ->comment('Reason if manager rejected the file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paid_users', function (Blueprint $table) {
            try { $table->dropForeign(['processed_by']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['reviewed_by']); } catch (\Throwable $e) {}
            $cols = array_filter(
                ['processed_by', 'reviewed_by', 'status', 'reviewed_at', 'rejection_reason'],
                fn($c) => Schema::hasColumn('paid_users', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
