<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('recovery_encrypted_private_key')->nullable()->after('key_salt');
            $table->string('recovery_key_iv')->nullable()->after('recovery_encrypted_private_key');
            $table->string('recovery_key_salt')->nullable()->after('recovery_key_iv');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['recovery_encrypted_private_key', 'recovery_key_iv', 'recovery_key_salt']);
        });
    }
};
