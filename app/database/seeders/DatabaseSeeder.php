<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use App\Models\Brand;
use App\Models\CarPart;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\Partner;
use App\Models\WithdrawalRequest;
use App\Models\Notification;
use App\Models\Inbox;
use App\Models\BrandUser;
use App\Models\AllowedApplicants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Create levels
        $this->createLevels();
        
        // Create users
        $this->createUsers();
        
        // Create brands and car parts
        $this->createBrandsAndCarParts();
        
        // Create proformas
        $this->createProformas();
        
        // Create partnerships
        $this->createPartnerships();
        
        // Create withdrawal requests
        $this->createWithdrawalRequests();
        
        // Create notifications and inbox
        $this->createNotificationsAndInbox();
        
        // Create brand user relationships
        $this->createBrandUserRelationships();
        
        // Create allowed applicants
        $this->createAllowedApplicants();

        // Add additional garages and shops
        $this->call(AddGaragesAndShopsSeeder::class);

        $this->command->info('Database seeding completed successfully!');
    }

    private function createLevels()
    {
        $this->command->info('Creating levels...');
        
        $levels = [
            ['name' => 'Operator', 'rank' => 2, 'status_label' => 'Payment Collected'],
            ['name' => 'Manager', 'rank' => 1, 'status_label' => 'Payment Verified']
        ];
        
        foreach ($levels as $level) {
            if (!Level::where('name', $level['name'])->exists()) {
                Level::create($level);
            }
        }
    }

    private function createUsers()
    {
        $this->command->info('Creating users...');
        
        $managerLevel = Level::where('rank', 1)->first();
        $operatorLevel = Level::where('rank', 2)->first();
        
        // Create 1 Super Admin
        if (!User::where('email', 'superadmin@etera.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@etera.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'approved' => true,
                'phone_number' => '251911234567',
                'balance' => 1000,
                'email_verified_at' => now(),
            ]);
        }

        // Create 3 Admins (2 approved, 1 pending)
        $adminEmails = ['admin1@etera.com', 'admin2@etera.com', 'admin3@etera.com'];
        $adminApprovals = [true, true, false];
        
        foreach ($adminEmails as $index => $email) {
            if (!User::where('email', $email)->exists()) {
                User::create([
                    'name' => 'Admin ' . ($index + 1),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'approved' => $adminApprovals[$index],
                    'phone_number' => '25191123456' . ($index + 1),
                    'balance' => 500,
                    'email_verified_at' => now(),
                ]);
            }
        }

        // Create users for each role category (6 each, half approved, half pending)
        $roles = ['insurance', 'business_owner', 'shop', 'garage', 'employee', 'marketer', 'individual'];

        foreach ($roles as $role) {
            $this->createUsersForRole($role, 6, $managerLevel, $operatorLevel);
        }

        // Create specific test users
        $this->createSpecificTestUsers($managerLevel, $operatorLevel);
    }

    private function createUsersForRole($role, $count, $managerLevel, $operatorLevel)
    {
        for ($i = 1; $i <= $count; $i++) {
            $approved = $i <= ($count / 2); // First half approved, second half pending
            $email = strtolower($role) . $i . '@etera.com';
            $phoneNumber = '251911' . str_pad(100000 + ($count * 10) + $i, 6, '0', STR_PAD_LEFT);
            
            if (!User::where('email', $email)->exists() && !User::where('phone_number', $phoneNumber)->exists()) {
                $userData = [
                    'name' => ucfirst($role) . ' User ' . $i,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'approved' => $approved,
                    'phone_number' => $phoneNumber,
                    'balance' => rand(100, 1000),
                    'email_verified_at' => now(),
                ];

                // Add level_id for employees
                if ($role === 'employee') {
                    $userData['level_id'] = $i <= 3 ? $managerLevel->id : $operatorLevel->id;
                }

                // For shop and garage users, generate unique store_id
                if (in_array($role, ['shop', 'garage'])) {
                    $prefix = $role === 'garage' ? 'EG-' : 'ES-';
                    $lastStoreId = User::where('role', $role)
                        ->whereNotNull('store_id')
                        ->orderBy('id', 'desc')
                        ->value('store_id');
                    
                    $nextNumber = $lastStoreId ? intval(substr($lastStoreId, 3)) + 1 : 1;
                    $userData['store_id'] = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                }

                User::create($userData);
            }
        }
    }

    private function createSpecificTestUsers($managerLevel, $operatorLevel)
    {
        $specificUsers = [
            [
                'name' => 'Test Manager',
                'email' => 'manager@etera.com',
                'role' => 'employee',
                'level_id' => $managerLevel->id,
                'approved' => true,
                'phone_number' => '251911999999',
            ],
            [
                'name' => 'Test Operator',
                'email' => 'operator@etera.com',
                'role' => 'employee',
                'level_id' => $operatorLevel->id,
                'approved' => true,
                'phone_number' => '251911999998',
            ],
            [
                'name' => 'Test Marketer',
                'email' => 'marketer@etera.com',
                'role' => 'marketer',
                'level_id' => $operatorLevel->id,
                'approved' => true,
                'phone_number' => '251911999997',
            ],
            [
                'name' => 'Test Insurance',
                'email' => 'nile@insurance.com',
                'role' => 'insurance',
                'approved' => true,
                'phone_number' => '251911999996',
            ],
            [
                'name' => 'Test Business Owner',
                'email' => 'businessowner@etera.com',
                'role' => 'business_owner',
                'approved' => true,
                'phone_number' => '251911999995',
            ],
            [
                'name' => 'Test Shop',
                'email' => 'shop@etera.com',
                'role' => 'shop',
                'approved' => true,
                'phone_number' => '251911999994',
            ],
            [
                'name' => 'Test Garage',
                'email' => 'garage@etera.com',
                'role' => 'garage',
                'approved' => true,
                'phone_number' => '251911999993',
            ],
        ];

        foreach ($specificUsers as $userData) {
            if (!User::where('email', $userData['email'])->exists() && !User::where('phone_number', $userData['phone_number'])->exists()) {
                $userData['password'] = Hash::make('password');
                $userData['balance'] = rand(200, 500);
                $userData['email_verified_at'] = now();
                
                User::create($userData);
            }
        }
    }

    private function createBrandsAndCarParts()
    {
        $this->command->info('Creating brands and car parts...');
        
        $brands = [
            'Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan', 'Volkswagen', 'BMW', 'Mercedes-Benz',
            'Hyundai', 'Kia', 'Mazda', 'Subaru', 'Renault', 'Audi', 'Peugeot', 'Citroën',
            'Skoda', 'Fiat', 'Volvo', 'Land Rover', 'Jaguar', 'Porsche', 'Lexus', 'Tesla',
            'Mitsubishi', 'Suzuki', 'Dacia', 'MINI', 'Jeep', 'Cadillac', 'Infiniti', 'Lincoln',
            'Buick', 'GMC', 'Dodge', 'Ram', 'Chrysler', 'Maserati', 'Aston Martin', 'Ferrari',
            'McLaren', 'Lotus', 'Morgan', 'Rolls-Royce', 'Bentley', 'Bugatti'
        ];

        foreach ($brands as $brandName) {
            if (!Brand::where('name', $brandName)->exists()) {
                Brand::create(['name' => $brandName]);
            }
        }

        $carParts = [
            'Engine', 'Transmission', 'Brakes', 'Tires', 'Wheels', 'Suspension', 'Exhaust System',
            'Battery', 'Alternator', 'Starter', 'Radiator', 'Air Conditioner', 'Headlights',
            'Taillights', 'Windshield', 'Doors', 'Seats', 'Steering Wheel', 'Dashboard',
            'Mirror', 'Fuel Tank', 'Fuel Pump', 'Sensors', 'Catalytic Converter', 'Oxygen Sensor',
            'Mass Airflow Sensor', 'Throttle Position Sensor', 'Crankshaft Position Sensor',
            'Cam Position Sensor', 'Spark Plugs', 'Fuel Injectors', 'Glow Plugs', 'Timing Belt',
            'Water Pump', 'Oil Filter', 'Air Filter', 'Cabin Air Filter', 'HVAC Filter',
            'Clutch', 'Flywheel', 'Driveshaft', 'Differential', 'Axles', 'CV Joints',
            'Shock Absorbers', 'Springs', 'Control Arms', 'Ball Joints', 'Tie Rod Ends',
            'Steering Rack', 'Anti-Roll Bars', 'Stabilizers', 'Muffler', 'Pipe', 'Converter',
            'Resonator', 'Tailpipe', 'Battery Cables', 'Fuses', 'Relays', 'Wiring Harness',
            'ECU (Engine Control Unit)', 'PCM (Powertrain Control Module)', 'BCM (Body Control Module)',
            'ABS (Anti-lock Brake System)', 'TCS (Traction Control System)', 'ESP (Electronic Stability Program)',
            'Cruise Control', 'Infotainment System', 'Navigation System', 'Bluetooth', 'USB Ports',
            'Speakers', 'Amplifier', 'Antenna', 'Sunroof', 'Moonroof', 'Spoiler', 'Grill',
            'Hood', 'Fender', 'Roof', 'Trunk', 'Bumper', 'Side Skirt', 'Interior Trim',
            'Exterior Trim', 'Emblem', 'Badge', 'Decal', 'Wheel Covers', 'Hubcaps', 'Lug Nuts',
            'Jack', 'Lug Wrench', 'Tire Pressure Monitoring System (TPMS)', 'First Aid Kit',
            'Fire Extinguisher', 'Tool Kit', 'Owner\'s Manual'
        ];

        $grades = ['A', 'B', 'C', 'D', 'E', 'F'];
        $components = ['Engine', 'Transmission', 'Electrical', 'Body', 'Interior', 'Exterior', 'Suspension', 'Brakes'];
        foreach ($carParts as $partName) {
            if (!CarPart::where('name', $partName)->exists()) {
                CarPart::create([
                    'name' => $partName,
                    'component' => $components[array_rand($components)],
                    'number' => rand(10000, 9999999),
                    'grade' => $grades[array_rand($grades)],
                ]);
            }
        }
    }

    private function createProformas()
    {
        $this->command->info('Creating proformas...');
        
        $brands = Brand::all();
        $carParts = CarPart::all();
        $insuranceUsers = User::where('role', 'insurance')->get();
        
        if ($insuranceUsers->isEmpty() || $brands->isEmpty() || $carParts->isEmpty()) {
            $this->command->warn('Skipping proforma creation - missing required data');
            return;
        }

                    // Create 5 proformas
            for ($i = 1; $i <= 5; $i++) {
                $proforma = Proforma::create([
                    'poster_id' => $insuranceUsers->random()->id,
                    'file_number' => 'PF' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'car_brand_id' => $brands->random()->id,
                    'model' => 'Model-' . $i,
                    'year' => rand(2015, 2024),
                    'customer_name' => 'Customer ' . $i,
                    'customer_phone_number' => '25191123456' . $i,
                    'license_plate_number' => 'ABC' . $i,
                    'chassis_number' => 'CHAS' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'status' => ['pending', 'opened', 'closed', 'published'][array_rand(['pending', 'opened', 'closed', 'published'])],
                ]);

            // Attach 3-6 random parts to each proforma
            $partsToAttach = $carParts->random(rand(3, 6));
            foreach ($partsToAttach as $part) {
                $proforma->parts()->attach($part->id, [
                    'number' => rand(1, 5),
                    'grade' => ['A', 'B', 'C'][array_rand(['A', 'B', 'C'])],
                    'photo' => null,
                ]);
            }

            // Create proforma applications
            $this->createProformaApplications($proforma, $carParts);
        }
    }

    private function createProformaApplications($proforma, $carParts)
    {
        $shops = User::where('role', 'shop')->get();
        $garages = User::where('role', 'garage')->get();
        $allPartners = $shops->merge($garages);

        if ($allPartners->isEmpty()) return;

        // Create 2-3 applications per proforma
        $numApplications = rand(2, 3);
        for ($i = 1; $i <= $numApplications; $i++) {
            $partner = $allPartners->random();
            
            $application = ProformaApplication::create([
                'proforma_id' => $proforma->id,
                'application_by' => $partner->id,
                'from' => $partner->role,
                'amount' => rand(5000, 50000),
            ]);

            // Create part prices for this application
            $this->createPartPrices($application, $carParts);
        }
    }

    private function createPartPrices($application, $carParts)
    {
        $parts = $carParts->random(rand(2, 4));
        
        foreach ($parts as $part) {
            ProformaPartPrice::create([
                'application_id' => $application->id,
                'car_part_id' => $part->id,
                'unit_price' => rand(100, 5000),
                'part_total' => rand(500, 10000),
            ]);
        }
    }

    private function createPartnerships()
    {
        $this->command->info('Creating partnerships...');
        
        $garages = User::where('role', 'garage')->get();
        $shops = User::where('role', 'shop')->get();
        $insuranceUsers = User::where('role', 'insurance')->get();

        foreach ($insuranceUsers as $insurance) {
            // Partner with random shops
            if ($shops->isNotEmpty()) {
                $randomShop = $shops->random();
                if (!Partner::where('partner_id', $randomShop->id)->where('insurance_id', $insurance->id)->exists()) {
                    Partner::create([
                        'insurance_id' => $insurance->id,
                        'partner_id' => $randomShop->id,
                    ]);
                }
            }

            // Partner with random garages
            if ($garages->isNotEmpty()) {
                $randomGarage = $garages->random();
                if (!Partner::where('partner_id', $randomGarage->id)->where('insurance_id', $insurance->id)->exists()) {
                    Partner::create([
                        'insurance_id' => $insurance->id,
                        'partner_id' => $randomGarage->id,
                    ]);
                }
            }
        }
    }

    private function createWithdrawalRequests()
    {
        $this->command->info('Creating withdrawal requests...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            if (!$user->withdrawalRequests()->exists()) {
                $user->withdrawalRequests()->create([
                    'amount' => rand(1000, 50000),
                    'account_number' => '1000022040420' . $user->id,
                    'bank_name' => ['CBE', 'Commercial Bank', 'Dashen Bank', 'Bank of Abyssinia'][array_rand(['CBE', 'Commercial Bank', 'Dashen Bank', 'Bank of Abyssinia'])],
                    'status' => ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])],
                ]);
            }
        }
    }

    private function createNotificationsAndInbox()
    {
        $this->command->info('Creating inbox relationships...');
        
        // The inboxes table is a relationship table between users and proformas
        // It's not a messaging system, so we'll skip creating inbox records
        // as they should be created when users interact with proformas
    }

    private function createBrandUserRelationships()
    {
        $this->command->info('Creating brand user relationships...');
        
        $users = User::whereIn('role', ['shop', 'garage'])->get();
        $brands = Brand::all();
        
        foreach ($users as $user) {
            $numBrands = rand(1, 3);
            $randomBrands = $brands->random($numBrands);
            
            foreach ($randomBrands as $brand) {
                if (!BrandUser::where('user_id', $user->id)->where('brand_id', $brand->id)->exists()) {
                    BrandUser::create([
                        'user_id' => $user->id,
                        'brand_id' => $brand->id,
                    ]);
                }
            }
        }
    }

    private function createAllowedApplicants()
    {
        $this->command->info('Creating allowed applicants...');
        
        $proformas = Proforma::all();
        $users = User::whereIn('role', ['shop', 'garage'])->get();
        
        foreach ($proformas as $proforma) {
            $availableUsers = $users->shuffle()->take(min(rand(2, 3), $users->count()));
            
            foreach ($availableUsers as $applicant) {
                if (!AllowedApplicants::where('proforma_id', $proforma->id)->where('applicant_id', $applicant->id)->exists()) {
                    AllowedApplicants::create([
                        'proforma_id' => $proforma->id,
                        'applicant_id' => $applicant->id,
                    ]);
                }
            }
        }
    }
}
