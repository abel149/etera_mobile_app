<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            // Remove old column
            if (Schema::hasColumn('costs', 'proforma_cost')) {
                $table->dropColumn('proforma_cost');
            }

            // Add new ones
            $table->decimal('1_proforma_cost', 10, 2)->nullable();
            $table->decimal('2_proforma_cost', 10, 2)->nullable();
            $table->decimal('3_proforma_cost', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->decimal('proforma_cost', 10, 2)->nullable();

            $table->dropColumn(['1_proforma_cost', '2_proforma_cost', '3_proforma_cost']);
        });
    }
};

