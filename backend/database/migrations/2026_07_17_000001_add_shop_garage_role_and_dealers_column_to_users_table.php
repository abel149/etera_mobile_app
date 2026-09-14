<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'dealers')) {
                $table->boolean('dealers')->default(0)->after('role');
            }
            if (!Schema::hasColumn('users', 'shop_garage')) {
                $table->boolean('shop_garage')->default(0)->after('dealers');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'dealers')) {
                $table->dropColumn('dealers');
            }
            if (Schema::hasColumn('users', 'shop_garage')) {
                $table->dropColumn('shop_garage');
            }
        });
    }
};
