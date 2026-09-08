<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_pdfs', function (Blueprint $table) {
            // Path on local disk (storage/app/application-files/).
            // Null for old records that still use the encrypted_pdf column as base64 in DB.
            $table->string('file_path')->nullable()->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('application_pdfs', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
