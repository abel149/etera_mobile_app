<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'file_quota')) {
                $table->integer('file_quota')->nullable()
                      ->comment('Maximum number of files operator can process');
            }
            if (!Schema::hasColumn('users', 'commission_per_file')) {
                $table->decimal('commission_per_file', 10, 2)->nullable()
                      ->comment('Commission amount operator earns per processed file');
            }
            if (!Schema::hasColumn('users', 'employee_type')) {
                $table->enum('employee_type', ['operator', 'manager'])->nullable()
                      ->comment('Type of employee: operator or manager');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['file_quota', 'commission_per_file', 'employee_type'],
                fn($c) => Schema::hasColumn('users', $c)
            );
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
