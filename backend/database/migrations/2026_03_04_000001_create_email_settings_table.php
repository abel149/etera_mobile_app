<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('enabled')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default email types
        DB::table('email_settings')->insert([
            ['key' => 'proforma_created',       'enabled' => true, 'description' => 'Email sent to admin when a new proforma is created',            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'proforma_floated',        'enabled' => true, 'description' => 'Email sent to shops when a proforma is published/floated',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'proforma_completed',      'enabled' => true, 'description' => 'Invoice email sent when proforma is verified/completed',        'created_at' => now(), 'updated_at' => now()],
            ['key' => 'proforma_closed_billing',  'enabled' => true, 'description' => 'Billing info email sent when proforma is closed',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'user_approved',           'enabled' => true, 'description' => 'Email sent to user when their account is approved',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'password_reset',          'enabled' => true, 'description' => 'Password reset email with OTP link',                             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'email_otp',               'enabled' => true, 'description' => 'Email OTP verification code',                                    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'send_to_owner',           'enabled' => true, 'description' => 'Email sent when proforma is sent back to owner',                 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
