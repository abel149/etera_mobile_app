<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            if (!Schema::hasColumn('sent_emails', 'body')) {
                $table->longText('body')->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            if (Schema::hasColumn('sent_emails', 'body')) {
                $table->dropColumn('body');
            }
        });
    }
};
