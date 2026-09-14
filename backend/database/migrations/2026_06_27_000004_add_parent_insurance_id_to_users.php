<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'parent_insurance_id')) {
                $table->unsignedBigInteger('parent_insurance_id')->nullable()->after('registered_by');
                $table->foreign('parent_insurance_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'parent_insurance_id')) {
                try { $table->dropForeign(['parent_insurance_id']); } catch (\Throwable $e) {}
                $table->dropColumn('parent_insurance_id');
            }
        });
    }
};
