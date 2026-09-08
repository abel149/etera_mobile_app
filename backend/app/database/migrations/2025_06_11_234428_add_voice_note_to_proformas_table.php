<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->text('voice_note')->nullable();
            $table->string('voice_note_path')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['voice_note', 'voice_note_path']);
        });
    }
};
