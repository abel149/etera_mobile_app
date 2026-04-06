<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
{
    Schema::table('proforma_invoices', function (Blueprint $table) {
        $table->enum('type', ['regular', 'etera_chereta', 'insurance'])->change();
    });
}

public function down()
{
    Schema::table('proforma_invoices', function (Blueprint $table) {
        $table->enum('type', ['regular', 'etera_chereta'])->change();
    });
}

    
};
