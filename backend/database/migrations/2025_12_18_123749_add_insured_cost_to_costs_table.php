<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->decimal('insured_cost', 10, 2)->default(0)->after('insurance_proforma');
        });
    }

    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->dropColumn('insured_cost');
        });
    }
};
