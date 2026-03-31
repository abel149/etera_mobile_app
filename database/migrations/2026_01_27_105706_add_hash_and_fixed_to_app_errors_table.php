<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_errors', function (Blueprint $table) {
            $table->string('hash', 32)->after('trace')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('app_errors', function (Blueprint $table) {
            $table->dropColumn(['hash', 'fixed']);
        });
    }
};
