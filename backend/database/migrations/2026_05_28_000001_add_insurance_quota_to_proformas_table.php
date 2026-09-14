<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('proformas', 'insurance_shop_quota')) {
                $table->unsignedTinyInteger('insurance_shop_quota')->nullable()
                      ->after('required_number_of_shops');
            }
            if (!Schema::hasColumn('proformas', 'insurance_garage_quota')) {
                $table->unsignedTinyInteger('insurance_garage_quota')->nullable()
                      ->after('required_number_of_garages');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $cols = array_filter(
                ['insurance_shop_quota', 'insurance_garage_quota'],
                fn($c) => Schema::hasColumn('proformas', $c)
            );
            if ($cols) { $table->dropColumn(array_values($cols)); }
        });
    }
};
