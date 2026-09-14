<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_selections', 'commission_earned')) {
                $table->decimal('commission_earned', 10, 2)->default(0)
                      ->comment('Commission amount earned by operator for this file');
            }
            if (!Schema::hasColumn('proforma_selections', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()
                      ->comment('When the operator closed/completed this file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_selections', function (Blueprint $table) {
            $cols = array_filter(
                ['commission_earned', 'closed_at'],
                fn($c) => Schema::hasColumn('proforma_selections', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
