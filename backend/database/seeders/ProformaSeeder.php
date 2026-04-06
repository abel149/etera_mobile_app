<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proforma;
use App\Models\CarPart;

class ProformaSeeder extends Seeder
{
    public function run()
    {
        // Create brands first (assuming they are already seeded or available in the database)
        $brands = \App\Models\Brand::all();
        $insurances = \App\Models\User::whereIn('role', ['insurance'])->get();

        // Seeder logic for Proformas
        $proformaData = [
            [
                'poster_id' => $insurances->random()->id,
                'file_number' => '43245',
                'brand_id' => $brands->random()->id,
                'model' => 'DEXMA',
                'year' => 2024,
                'customer_name' => 'John Doe',
                'customer_phone_number' => '251945565261',
                'license_plate_number' => 'ABC123',
                'chassis_number' => 'CHAS123456',
            ],
            [
                'poster_id' => $insurances->random()->id,
                'file_number' => '12345',
                'brand_id' => $brands->random()->id,
                'model' => 'Alpha',
                'year' => 2023,
                'customer_name' => 'Jane Smith',
                'customer_phone_number' => '251912345678',
                'license_plate_number' => 'XYZ789',
                'chassis_number' => 'CHAS654321',
            ],
        ];

        $carParts = \App\Models\CarPart::all();

        // Create Proformas and attach parts to each one
        foreach ($proformaData as $proformaInfo) {
            $proforma = Proforma::create([
                'poster_id' => $proformaInfo['poster_id'],
                'file_number' => $proformaInfo['file_number'],
                'car_brand_id' => $proformaInfo['brand_id'],
                'model' => $proformaInfo['model'],
                'year' => $proformaInfo['year'],
                'customer_name' => $proformaInfo['customer_name'],
                'customer_phone_number' => $proformaInfo['customer_phone_number'],
                'license_plate_number' => $proformaInfo['license_plate_number'],
                'chassis_number' => $proformaInfo['chassis_number'],
            ]);

            // Attach random parts to the proforma with random data for the pivot table
            $partsToAttach = $carParts->random(rand(3, 6)); // Attach between 3 and 6 random parts

            foreach ($partsToAttach as $part) {
                $proforma->parts()->attach($part->id, [
                    'number' => rand(1, 5), // Random quantity of the part
                    'grade' => ['A', 'B', 'C'][array_rand(['A', 'B', 'C'])], // Random grade
                    'photo' => null, // You can add logic to handle photo uploads if needed
                ]);
            }
        }
    }
}
