<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_applications', 'inbox_group')) {
                $table->unsignedTinyInteger('inbox_group')->nullable()->after('application_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_applications', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_applications', 'inbox_group')) {
                $table->dropColumn('inbox_group');
            }
        });
    }
};
