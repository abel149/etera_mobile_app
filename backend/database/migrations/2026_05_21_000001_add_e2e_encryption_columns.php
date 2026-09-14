<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'public_key')) {
                $table->text('public_key')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'encrypted_private_key')) {
                $table->text('encrypted_private_key')->nullable()->after('public_key');
            }
            if (!Schema::hasColumn('users', 'key_iv')) {
                $table->string('key_iv', 255)->nullable()->after('encrypted_private_key');
            }
            if (!Schema::hasColumn('users', 'key_salt')) {
                $table->string('key_salt', 255)->nullable()->after('key_iv');
            }
            if (!Schema::hasColumn('users', 'has_encryption')) {
                $table->boolean('has_encryption')->default(false)->after('key_salt');
            }
        });

        Schema::table('proforma_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_applications', 'encrypted_amount')) {
                $table->text('encrypted_amount')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('proforma_applications', 'amount_is_encrypted')) {
                $table->boolean('amount_is_encrypted')->default(false)->after('encrypted_amount');
            }
        });

        Schema::table('proforma_part_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_part_prices', 'encrypted_unit_price')) {
                $table->text('encrypted_unit_price')->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('proforma_part_prices', 'encrypted_part_total')) {
                $table->text('encrypted_part_total')->nullable()->after('part_total');
            }
            if (!Schema::hasColumn('proforma_part_prices', 'price_is_encrypted')) {
                $table->boolean('price_is_encrypted')->default(false)->after('encrypted_part_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['public_key', 'encrypted_private_key', 'key_iv', 'key_salt', 'has_encryption'],
                fn($c) => Schema::hasColumn('users', $c)
            );
            if ($cols) { $table->dropColumn(array_values($cols)); }
        });

        Schema::table('proforma_applications', function (Blueprint $table) {
            $cols = array_filter(
                ['encrypted_amount', 'amount_is_encrypted'],
                fn($c) => Schema::hasColumn('proforma_applications', $c)
            );
            if ($cols) { $table->dropColumn(array_values($cols)); }
        });

        Schema::table('proforma_part_prices', function (Blueprint $table) {
            $cols = array_filter(
                ['encrypted_unit_price', 'encrypted_part_total', 'price_is_encrypted'],
                fn($c) => Schema::hasColumn('proforma_part_prices', $c)
            );
            if ($cols) { $table->dropColumn(array_values($cols)); }
        });
    }
};
