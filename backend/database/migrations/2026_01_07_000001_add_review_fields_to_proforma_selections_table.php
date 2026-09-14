<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_selections', 'review_status')) {
                $table->enum('review_status', ['pending', 'approved', 'rejected'])
                      ->nullable()->after('closed_at');
            }
            if (!Schema::hasColumn('proforma_selections', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()
                      ->constrained('users')->onDelete('set null')
                      ->after('review_status');
            }
            if (!Schema::hasColumn('proforma_selections', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('proforma_selections', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            try { $table->dropForeign(['reviewed_by']); } catch (\Throwable $e) {}
            $cols = array_filter(
                ['review_status', 'reviewed_by', 'reviewed_at', 'rejection_reason'],
                fn($c) => Schema::hasColumn('proforma_selections', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
