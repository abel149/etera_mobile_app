<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_part', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_part', 'name')) {
                $table->string('name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_part', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_part', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
