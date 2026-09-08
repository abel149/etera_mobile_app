<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the role column exists and has the old enum values
        if (Schema::hasColumn('users', 'role')) {
            // Get the current enum values from the database
            $currentRoleValues = $this->getCurrentRoleValues();
            
            // Only update if superadmin is not in the current values
            if (!in_array('superadmin', $currentRoleValues)) {
                Schema::table('users', function (Blueprint $table) {
                    // Drop the existing enum and recreate it with superadmin
                    $table->dropColumn('role');
                });
                
                Schema::table('users', function (Blueprint $table) {
                    $table->enum('role', ['superadmin', 'admin', 'business_owner', 'insurance', 'shop', 'garage', 'employee', 'marketer', 'individual'])->default('employee')->after('business_license_number');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only revert if we actually made changes
        if (Schema::hasColumn('users', 'role')) {
            $currentRoleValues = $this->getCurrentRoleValues();
            
            if (in_array('superadmin', $currentRoleValues)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('role');
                });
                
                Schema::table('users', function (Blueprint $table) {
                    $table->enum('role', ['admin', 'business_owner', 'insurance', 'shop', 'garage', 'employee', 'marketer'])->default('employee')->after('business_license_number');
                });
            }
        }
    }

    /**
     * Get the current role enum values from the database
     */
    private function getCurrentRoleValues(): array
    {
        try {
            $connection = Schema::getConnection();
            $table = $connection->getTablePrefix() . 'users';
            
            // Get the column definition
            $column = $connection->select("SHOW COLUMNS FROM {$table} WHERE Field = 'role'");
            
            if (!empty($column)) {
                $type = $column[0]->Type;
                // Extract enum values from the type string like "enum('admin','business_owner',...)"
                if (preg_match("/^enum\((.*)\)$/", $type, $matches)) {
                    $values = str_getcsv($matches[1], ',', "'");
                    return array_map('trim', $values);
                }
            }
        } catch (\Exception $e) {
            // If we can't determine the current values, assume we need to update
        }
        
        // Default to old values if we can't determine current ones
        return ['admin', 'business_owner', 'insurance', 'shop', 'garage', 'employee', 'marketer'];
    }
};
