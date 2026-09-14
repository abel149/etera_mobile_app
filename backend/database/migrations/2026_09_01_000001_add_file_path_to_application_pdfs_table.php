<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_pdfs', function (Blueprint $table) {
            if (!Schema::hasColumn('application_pdfs', 'file_path')) {
                $table->string('file_path')->nullable()->after('original_filename');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_pdfs', function (Blueprint $table) {
            if (Schema::hasColumn('application_pdfs', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};
