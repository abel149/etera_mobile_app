<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add dealers column
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dealers')->default(0)->after('role');
        });

        // Add shop_garage column
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('shop_garage')->default(0)->after('dealers');
        });
    }

    public function down(): void
    {
        // Remove dealers column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dealers');
        });

        // Remove shop_garage column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('shop_garage');
        });
    }
};
 