<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a superadmin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@etera.com',
            'password' => Hash::make('password123'),
            'phone_number' => '+251900000000',
            'location' => 'Addis Ababa, Ethiopia',
            'role' => 'admin',
            'is_superadmin' => true,
            'approved' => true,
            'approved_at' => now(),
        ]);

        // Create a regular admin user
        User::create([
            'name' => 'Regular Admin',
            'email' => 'admin@etera.com',
            'password' => Hash::make('password123'),
            'phone_number' => '+251900000001',
            'location' => 'Addis Ababa, Ethiopia',
            'role' => 'admin',
            'is_superadmin' => false,
            'approved' => true,
            'approved_at' => now(),
        ]);

        $this->command->info('Super Admin and Regular Admin users created successfully!');
        $this->command->info('Super Admin: superadmin@etera.com / password123');
        $this->command->info('Regular Admin: admin@etera.com / password123');
    }
}
