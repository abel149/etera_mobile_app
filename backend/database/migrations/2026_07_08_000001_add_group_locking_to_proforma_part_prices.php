<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_part_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_part_prices', 'proforma_id')) {
                $table->unsignedBigInteger('proforma_id')->nullable()->after('id');
                $table->foreign('proforma_id')->references('id')->on('proformas')->nullOnDelete();
            }
            if (!Schema::hasColumn('proforma_part_prices', 'inbox_group')) {
                $table->unsignedTinyInteger('inbox_group')->nullable()->after('proforma_id');
            }
        });

        // Backfill proforma_id from the related application
        DB::statement('
            UPDATE proforma_part_prices ppp
            JOIN proforma_applications pa ON pa.id = ppp.application_id
            SET ppp.proforma_id = pa.proforma_id
            WHERE ppp.proforma_id IS NULL
        ');

        // Backfill inbox_group from the related application
        DB::statement('
            UPDATE proforma_part_prices ppp
            JOIN proforma_applications pa ON pa.id = ppp.application_id
            SET ppp.inbox_group = pa.inbox_group
            WHERE ppp.inbox_group IS NULL AND pa.inbox_group IS NOT NULL
        ');

        // Deduplicate: for any (proforma_id, inbox_group, car_part_id) with multiple rows,
        // keep the newest (highest id) and delete the older ones before adding the constraint.
        DB::statement('
            DELETE ppp FROM proforma_part_prices ppp
            INNER JOIN (
                SELECT MAX(id) AS keep_id, proforma_id, inbox_group, car_part_id
                FROM proforma_part_prices
                WHERE proforma_id IS NOT NULL
                  AND inbox_group IS NOT NULL
                GROUP BY proforma_id, inbox_group, car_part_id
                HAVING COUNT(*) > 1
            ) dupes ON ppp.proforma_id = dupes.proforma_id
                    AND ppp.inbox_group  = dupes.inbox_group
                    AND ppp.car_part_id  = dupes.car_part_id
                    AND ppp.id          != dupes.keep_id
        ');

        // Unique constraint: prevents two prices for the same part in the same group.
        // NULL inbox_group is excluded from MySQL unique enforcement (NULLs != NULLs).
        // Check via information_schema so re-running the migration is safe.
        $indexExists = DB::select("
            SELECT 1 FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'proforma_part_prices'
              AND INDEX_NAME   = 'uq_group_part_price'
            LIMIT 1
        ");
        if (empty($indexExists)) {
            Schema::table('proforma_part_prices', function (Blueprint $table) {
                $table->unique(['proforma_id', 'inbox_group', 'car_part_id'], 'uq_group_part_price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $table->dropUnique('uq_group_part_price');
            $table->dropForeign(['proforma_id']);
            $table->dropColumn(['proforma_id', 'inbox_group']);
        });
    }
};
