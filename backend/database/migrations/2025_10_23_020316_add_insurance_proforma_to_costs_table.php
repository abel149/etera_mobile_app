<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            if (!Schema::hasColumn('costs', 'insurance_proforma')) {
                $table->decimal('insurance_proforma', 10, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            if (Schema::hasColumn('costs', 'insurance_proforma')) {
                $table->dropColumn('insurance_proforma');
            }
        });
    }
};
