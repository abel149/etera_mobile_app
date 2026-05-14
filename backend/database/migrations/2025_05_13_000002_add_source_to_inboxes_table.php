<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inboxes', function (Blueprint $table) {
            // 'insurance' = created by insurance during proforma creation (partner slot)
            // 'admin'     = created by admin after creation (dedicated individual slot)
            $table->string('source', 20)->default('insurance')->after('proforma_id');
        });

        // Backfill existing rows:
        // Non-insurance proformas had no insurance partner concept → treat old inboxes as 'admin'
        // Insurance proformas already default to 'insurance' — no update needed for them
        \Illuminate\Support\Facades\DB::statement("
            UPDATE inboxes
            SET source = 'admin'
            WHERE proforma_id IN (
                SELECT proformas.id FROM proformas
                INNER JOIN users ON users.id = proformas.poster_id
                WHERE users.role != 'insurance'
            )
        ");
    }

    public function down(): void
    {
        Schema::table('inboxes', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
