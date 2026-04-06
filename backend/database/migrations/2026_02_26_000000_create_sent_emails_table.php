<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->string('type');                     // e.g. 'proforma_floated', 'application_received', 'application_submitted'
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('proforma_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->default('sent');  // 'sent', 'failed'
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('proforma_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
