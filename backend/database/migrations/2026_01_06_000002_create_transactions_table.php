<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type'); // deposit, withdrawal, commission, invoice, payment, adjustment
                $table->decimal('amount', 15, 4); // Positive = Credit, Negative = Debit
                $table->decimal('balance_after', 15, 4);
                $table->nullableMorphs('reference'); // reference_type, reference_id
                $table->string('description');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 15, 4)->default(0)->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wallet_balance')) {
                $table->dropColumn('wallet_balance');
            }
        });

        Schema::dropIfExists('transactions');
    }
};
