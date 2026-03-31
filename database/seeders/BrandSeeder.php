<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Toyota',
            'Honda',
            'Ford',
            'Chevrolet',
            'Nissan',
            'Volkswagen',
            'BMW',
            'Mercedes-Benz',
            'Hyundai',
            'Kia',
            'Mazda',
            'Subaru',
            'Renault',
            'Audi',
            'Peugeot',
            'Citroën',
            'Skoda',
            'Fiat',
            'Volvo',
            'Land Rover',
            'Jaguar',
            'Porsche',
            'Lexus',
            'Tesla',
            'Mitsubishi',
            'Suzuki',
            'Dacia',
            'MINI',
            'Jeep',
            'Cadillac',
            'Infiniti',
            'Lincoln',
            'Buick',
            'GMC',
            'Dodge',
            'Ram',
            'Chrysler',
            'Maserati',
            'Aston Martin',
            'Ferrari',
            'McLaren',
            'Lotus',
            'Morgan',
            'Rolls-Royce',
            'Bentley',
            'Bugatti',
        ];

        foreach ($brands as $brandName) {
            Brand::create(['name' => $brandName]);
        }
    }
}
