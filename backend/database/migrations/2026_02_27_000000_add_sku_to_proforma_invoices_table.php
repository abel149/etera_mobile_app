<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_invoices', 'sku')) {
                $table->string('sku', 8)->unique()->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_invoices', 'sku')) {
                $table->dropColumn('sku');
            }
        });
    }
};
