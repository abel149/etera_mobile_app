<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'damage_severity')) {
                $table->enum('damage_severity', ['minor', 'major', 'severe'])
                      ->nullable()->after('car_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (Schema::hasColumn('proformas', 'damage_severity')) {
                $table->dropColumn('damage_severity');
            }
        });
    }
};
