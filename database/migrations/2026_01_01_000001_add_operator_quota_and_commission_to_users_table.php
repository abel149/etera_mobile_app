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
        Schema::table('users', function (Blueprint $table) {
            // Add operator-specific fields
            $table->integer('file_quota')->nullable()->comment('Maximum number of files operator can process');
            $table->decimal('commission_per_file', 10, 2)->nullable()->comment('Commission amount operator earns per processed file');
            $table->enum('employee_type', ['operator', 'manager'])->nullable()->comment('Type of employee: operator or manager');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['file_quota', 'commission_per_file', 'employee_type']);
        });
    }
};
