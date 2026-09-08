<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── users: store RSA public key + AES-wrapped private key ──────────
        Schema::table('users', function (Blueprint $table) {
            $table->text('public_key')->nullable()->after('password');
            $table->text('encrypted_private_key')->nullable()->after('public_key');
            $table->string('key_iv', 255)->nullable()->after('encrypted_private_key');
            $table->string('key_salt', 255)->nullable()->after('key_iv');
            $table->boolean('has_encryption')->default(false)->after('key_salt');
        });

        // ── proforma_applications: encrypted garage amount ─────────────────
        Schema::table('proforma_applications', function (Blueprint $table) {
            $table->text('encrypted_amount')->nullable()->after('amount');
            $table->boolean('amount_is_encrypted')->default(false)->after('encrypted_amount');
        });

        // ── proforma_part_prices: encrypted shop unit prices ───────────────
        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $table->text('encrypted_unit_price')->nullable()->after('unit_price');
            $table->text('encrypted_part_total')->nullable()->after('part_total');
            $table->boolean('price_is_encrypted')->default(false)->after('encrypted_part_total');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_key', 'encrypted_private_key', 'key_iv', 'key_salt', 'has_encryption']);
        });

        Schema::table('proforma_applications', function (Blueprint $table) {
            $table->dropColumn(['encrypted_amount', 'amount_is_encrypted']);
        });

        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $table->dropColumn(['encrypted_unit_price', 'encrypted_part_total', 'price_is_encrypted']);
        });
    }
};
