<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'is_new')) {
                $table->boolean('is_new')->default(true)->after('verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (Schema::hasColumn('proformas', 'is_new')) {
                $table->dropColumn('is_new');
            }
        });
    }
};
