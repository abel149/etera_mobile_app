<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proforma_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_applications', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proforma_applications', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_applications', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });
    }
};
