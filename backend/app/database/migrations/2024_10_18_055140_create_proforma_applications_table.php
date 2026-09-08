<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proforma_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Proforma::class, 'proforma_id');
            $table->foreignIdFor(\App\Models\User::class, 'application_by');
            $table->enum('from', ['garage','shop'])->default('garage');
            $table->string('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_applications');
    }
};
