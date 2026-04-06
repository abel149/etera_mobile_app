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
        Schema::table('proformas', function (Blueprint $table) {
            // Drop the existing voice_note column if it exists
            if (Schema::hasColumn('proformas', 'voice_note')) {
                $table->dropColumn('voice_note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            // Add back the original voice_note column as TEXT
            if (!Schema::hasColumn('proformas', 'voice_note')) {
                $table->text('voice_note')->nullable()->after('model');
            }
        });
    }
};
