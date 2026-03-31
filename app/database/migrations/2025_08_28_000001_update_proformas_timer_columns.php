<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('proformas', function (Blueprint $table) {
			if (Schema::hasColumn('proformas', 'timer_enabled')) {
				$table->dropColumn('timer_enabled');
			}
			if (Schema::hasColumn('proformas', 'timer_type')) {
				$table->dropColumn('timer_type');
			}
		});
	}

	public function down(): void
	{
		Schema::table('proformas', function (Blueprint $table) {
			if (!Schema::hasColumn('proformas', 'timer_enabled')) {
				$table->boolean('timer_enabled')->default(false)->after('required_number_of_garages');
			}
			if (!Schema::hasColumn('proformas', 'timer_type')) {
				$table->enum('timer_type', ['fixed','unlimited'])->nullable()->after('timer_enabled');
			}
		});
	}
}; 