<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddGaragesAndShopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding 5 garages and 5 shops...');

        // Create 5 Garages (3 approved, 2 not approved)
        $this->createGarages();

        // Create 5 Shops (3 approved, 2 not approved)
        $this->createShops();

        $this->command->info('Garages and shops added successfully!');
    }

    private function createGarages()
    {
        $this->command->info('Creating 5 garages...');

        $garages = [
            [
                'name' => 'Addis Auto Garage',
                'email' => 'addis.garage@etera.com',
                'phone_number' => '251911234001',
                'store_id' => 'EG-0001',
                'tin_number' => 'TIN001001',
                'location' => 'Addis Ababa, Bole',
                'approved' => true,
            ],
            [
                'name' => 'Central Repair Center',
                'email' => 'central.garage@etera.com',
                'phone_number' => '251911234002',
                'store_id' => 'EG-0002',
                'tin_number' => 'TIN001002',
                'location' => 'Addis Ababa, Kazanchis',
                'approved' => true,
            ],
            [
                'name' => 'Premium Auto Service',
                'email' => 'premium.garage@etera.com',
                'phone_number' => '251911234003',
                'store_id' => 'EG-0003',
                'tin_number' => 'TIN001003',
                'location' => 'Addis Ababa, CMC',
                'approved' => true,
            ],
            [
                'name' => 'Quick Fix Garage',
                'email' => 'quickfix.garage@etera.com',
                'phone_number' => '251911234004',
                'store_id' => 'EG-0004',
                'tin_number' => 'TIN001004',
                'location' => 'Addis Ababa, Kolfe',
                'approved' => false,
            ],
            [
                'name' => 'Express Auto Care',
                'email' => 'express.garage@etera.com',
                'phone_number' => '251911234005',
                'store_id' => 'EG-0005',
                'tin_number' => 'TIN001005',
                'location' => 'Addis Ababa, Yeka',
                'approved' => false,
            ],
        ];

        foreach ($garages as $garage) {
            if (!User::where('email', $garage['email'])->exists() && 
                !User::where('store_id', $garage['store_id'])->exists()) {
                User::create([
                    'name' => $garage['name'],
                    'email' => $garage['email'],
                    'password' => Hash::make('password'),
                    'role' => 'garage',
                    'approved' => $garage['approved'],
                    'phone_number' => $garage['phone_number'],
                    'store_id' => $garage['store_id'],
                    'tin_number' => $garage['tin_number'],
                    'location' => $garage['location'],
                    'balance' => rand(500, 2000),
                    'email_verified_at' => now(),
                ]);
            }
        }
    }

    private function createShops()
    {
        $this->command->info('Creating 5 shops...');

        $shops = [
            [
                'name' => 'Addis Spare Parts',
                'email' => 'addis.shop@etera.com',
                'phone_number' => '251911234006',
                'store_id' => 'ES-0001',
                'tin_number' => 'TIN002001',
                'location' => 'Addis Ababa, Mercato',
                'approved' => true,
            ],
            [
                'name' => 'Central Auto Parts',
                'email' => 'central.shop@etera.com',
                'phone_number' => '251911234007',
                'store_id' => 'ES-0002',
                'tin_number' => 'TIN002002',
                'location' => 'Addis Ababa, Piassa',
                'approved' => true,
            ],
            [
                'name' => 'Premium Parts Store',
                'email' => 'premium.shop@etera.com',
                'phone_number' => '251911234008',
                'store_id' => 'ES-0003',
                'tin_number' => 'TIN002003',
                'location' => 'Addis Ababa, Bole',
                'approved' => true,
            ],
            [
                'name' => 'Quick Parts Shop',
                'email' => 'quickparts.shop@etera.com',
                'phone_number' => '251911234009',
                'store_id' => 'ES-0004',
                'tin_number' => 'TIN002004',
                'location' => 'Addis Ababa, Kolfe',
                'approved' => false,
            ],
            [
                'name' => 'Express Parts Center',
                'email' => 'expressparts.shop@etera.com',
                'phone_number' => '251911234010',
                'store_id' => 'ES-0005',
                'tin_number' => 'TIN002005',
                'location' => 'Addis Ababa, Yeka',
                'approved' => false,
            ],
        ];

        foreach ($shops as $shop) {
            if (!User::where('email', $shop['email'])->exists() && 
                !User::where('store_id', $shop['store_id'])->exists()) {
                User::create([
                    'name' => $shop['name'],
                    'email' => $shop['email'],
                    'password' => Hash::make('password'),
                    'role' => 'shop',
                    'approved' => $shop['approved'],
                    'phone_number' => $shop['phone_number'],
                    'store_id' => $shop['store_id'],
                    'tin_number' => $shop['tin_number'],
                    'location' => $shop['location'],
                    'balance' => rand(1000, 3000),
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
