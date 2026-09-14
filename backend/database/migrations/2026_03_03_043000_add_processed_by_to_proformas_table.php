<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('poster_id');
                $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (Schema::hasColumn('proformas', 'processed_by')) {
                try { $table->dropForeign(['processed_by']); } catch (\Throwable $e) {}
                $table->dropColumn('processed_by');
            }
        });
    }
};
