<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Proforma;
use App\Models\User;
use App\Models\ProformaApplication;
use App\Models\Brand;
use App\Models\CarPart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProformaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->insurance = User::factory()->create(['role' => 'insurance']);
        $this->shop = User::factory()->create(['role' => 'shop']);
        $this->garage = User::factory()->create(['role' => 'garage']);
        $this->brand = Brand::factory()->create();
        $this->carPart = CarPart::factory()->create();
    }

    /** @test */
    public function it_can_create_a_regular_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-001',
            'customer_name' => 'John Doe',
            'customer_phone_number' => '+1234567890',
            'car_brand_id' => $this->brand->id,
            'model' => 'Civic',
            'year' => '2020',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'ABC123',
            'chassis_number' => 'CHASSIS123',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('proformas', [
            'id' => $proforma->id,
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
        ]);

        $this->assertFalse($proforma->isEteraCheretaMode());
        $this->assertEquals(5, $proforma->number_of_proformas);
    }

    /** @test */
    public function it_can_create_etera_chereta_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-002',
            'customer_name' => 'Jane Doe',
            'customer_phone_number' => '+1234567891',
            'car_brand_id' => $this->brand->id,
            'model' => 'Accord',
            'year' => '2021',
            'required_number_of_shops' => 0, // Etera-Chereta mode
            'required_number_of_garages' => 0,
            'license_plate_number' => 'XYZ789',
            'chassis_number' => 'CHASSIS456',
            'timer_duration' => 3600, // 1 hour
            'timer_expires_at' => now()->addHour(),
            'status' => 'pending',
        ]);

        $this->assertTrue($proforma->isEteraCheretaMode());
        $this->assertEquals('∞', $proforma->remaining_shops);
        $this->assertFalse($proforma->isTimerExpired());
    }

    /** @test */
    public function it_can_detect_timer_expiration()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-003',
            'customer_name' => 'Bob Smith',
            'customer_phone_number' => '+1234567892',
            'car_brand_id' => $this->brand->id,
            'model' => 'CR-V',
            'year' => '2022',
            'required_number_of_shops' => 0,
            'required_number_of_garages' => 0,
            'license_plate_number' => 'DEF456',
            'chassis_number' => 'CHASSIS789',
            'timer_duration' => 3600,
            'timer_expires_at' => now()->subHour(), // Expired
            'status' => 'pending',
        ]);

        $this->assertTrue($proforma->isTimerExpired());
        $this->assertEquals('Expired', $proforma->getFormattedRemainingTime());
    }

    /** @test */
    public function it_can_calculate_remaining_time()
    {
        $futureTime = now()->addMinutes(30);
        
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-004',
            'customer_name' => 'Alice Johnson',
            'customer_phone_number' => '+1234567893',
            'car_brand_id' => $this->brand->id,
            'model' => 'Pilot',
            'year' => '2023',
            'required_number_of_shops' => 0,
            'required_number_of_garages' => 0,
            'license_plate_number' => 'GHI789',
            'chassis_number' => 'CHASSIS012',
            'timer_duration' => 3600,
            'timer_expires_at' => $futureTime,
            'status' => 'pending',
        ]);

        $remainingTime = $proforma->getRemainingTime();
        
        // The remaining time should be positive and close to 30 minutes (1800 seconds)
        $this->assertGreaterThan(0, $remainingTime);
        $this->assertLessThanOrEqual(1800, $remainingTime);
        $this->assertGreaterThan(1700, $remainingTime);
    }

    /** @test */
    public function it_can_check_if_user_can_apply_to_regular_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-005',
            'customer_name' => 'Charlie Brown',
            'customer_phone_number' => '+1234567894',
            'car_brand_id' => $this->brand->id,
            'model' => 'Odyssey',
            'year' => '2024',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'JKL012',
            'chassis_number' => 'CHASSIS345',
            'status' => 'pending',
        ]);

        $this->assertTrue($proforma->isApplicableBy($this->shop));
        $this->assertTrue($proforma->isApplicableBy($this->garage));
    }

    /** @test */
    public function it_can_check_if_user_can_apply_to_etera_chereta_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-006',
            'customer_name' => 'Diana Prince',
            'customer_phone_number' => '+1234567895',
            'car_brand_id' => $this->brand->id,
            'model' => 'Ridgeline',
            'year' => '2025',
            'required_number_of_shops' => 0,
            'required_number_of_garages' => 0,
            'license_plate_number' => 'MNO345',
            'chassis_number' => 'CHASSIS678',
            'timer_duration' => 3600,
            'timer_expires_at' => now()->addHour(),
            'status' => 'pending',
        ]);

        $this->assertTrue($proforma->isApplicableBy($this->shop));
        // Garages can only apply to regular insurance proformas, not Etera-Chereta mode
        $this->assertFalse($proforma->isApplicableBy($this->garage));
    }

    /** @test */
    public function it_prevents_applications_to_expired_etera_chereta_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-007',
            'customer_name' => 'Eve Wilson',
            'customer_phone_number' => '+1234567896',
            'car_brand_id' => $this->brand->id,
            'model' => 'Passport',
            'year' => '2026',
            'required_number_of_shops' => 0,
            'required_number_of_garages' => 0,
            'license_plate_number' => 'PQR678',
            'chassis_number' => 'CHASSIS901',
            'timer_duration' => 3600,
            'timer_expires_at' => now()->subHour(), // Expired
            'status' => 'pending',
        ]);

        $this->assertFalse($proforma->isApplicableBy($this->shop));
        $this->assertFalse($proforma->isApplicableBy($this->garage));
    }

    /** @test */
    public function it_can_track_applications_from_shops()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-008',
            'customer_name' => 'Frank Miller',
            'customer_phone_number' => '+1234567897',
            'car_brand_id' => $this->brand->id,
            'model' => 'Insight',
            'year' => '2027',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'STU901',
            'chassis_number' => 'CHASSIS234',
            'status' => 'pending',
        ]);

        // Create applications
        ProformaApplication::create([
            'proforma_id' => $proforma->id,
            'application_by' => $this->shop->id,
            'from' => 'shop',
            'amount' => 1000,
        ]);

        $this->assertEquals(1, $proforma->applicationsFromShops()->count());
        $this->assertEquals(2, $proforma->remaining_shops);
    }

    /** @test */
    public function it_can_track_applications_from_garages()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-009',
            'customer_name' => 'Grace Lee',
            'customer_phone_number' => '+1234567898',
            'car_brand_id' => $this->brand->id,
            'model' => 'Clarity',
            'year' => '2028',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'VWX234',
            'chassis_number' => 'CHASSIS567',
            'status' => 'pending',
        ]);

        // Create applications
        ProformaApplication::create([
            'proforma_id' => $proforma->id,
            'application_by' => $this->garage->id,
            'from' => 'garage',
            'amount' => 1500,
        ]);

        $this->assertEquals(1, $proforma->applicationsFromGarages()->count());
        $this->assertEquals(1, $proforma->remaining_garages);
    }

    /** @test */
    public function it_can_check_if_user_already_applied()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-010',
            'customer_name' => 'Henry Ford',
            'customer_phone_number' => '+1234567899',
            'car_brand_id' => $this->brand->id,
            'model' => 'Element',
            'year' => '2029',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'YZA567',
            'chassis_number' => 'CHASSIS890',
            'status' => 'pending',
        ]);

        ProformaApplication::create([
            'proforma_id' => $proforma->id,
            'application_by' => $this->shop->id,
            'from' => 'shop',
            'amount' => 1000,
        ]);

        $this->assertTrue($proforma->userAlreadyApplied($this->shop->id));
        $this->assertFalse($proforma->userAlreadyApplied($this->garage->id));
    }

    /** @test */
    public function it_can_filter_proformas_from_insurances()
    {
        $businessOwner = User::factory()->create(['role' => 'business_owner']);
        
        $insuranceProforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-011',
            'customer_name' => 'Ivy Chen',
            'customer_phone_number' => '+1234567900',
            'car_brand_id' => $this->brand->id,
            'model' => 'S2000',
            'year' => '2030',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'BCD890',
            'chassis_number' => 'CHASSIS123',
            'status' => 'pending',
        ]);

        $businessProforma = Proforma::create([
            'poster_id' => $businessOwner->id,
            'file_number' => 'TEST-012',
            'customer_name' => 'Jack Black',
            'customer_phone_number' => '+1234567901',
            'car_brand_id' => $this->brand->id,
            'model' => 'NSX',
            'year' => '2031',
            'required_number_of_shops' => 2,
            'required_number_of_garages' => 1,
            'license_plate_number' => 'EFG123',
            'chassis_number' => 'CHASSIS456',
            'status' => 'pending',
        ]);

        $insuranceProformas = Proforma::fromInsurances()->get();
        
        $this->assertTrue($insuranceProformas->contains($insuranceProforma));
        $this->assertFalse($insuranceProformas->contains($businessProforma));
    }

    /** @test */
    public function it_can_filter_proformas_from_others()
    {
        $businessOwner = User::factory()->create(['role' => 'business_owner']);
        
        $insuranceProforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-013',
            'customer_name' => 'Kate White',
            'customer_phone_number' => '+1234567902',
            'car_brand_id' => $this->brand->id,
            'model' => 'Legend',
            'year' => '2032',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'HIJ456',
            'chassis_number' => 'CHASSIS789',
            'status' => 'pending',
        ]);

        $businessProforma = Proforma::create([
            'poster_id' => $businessOwner->id,
            'file_number' => 'TEST-014',
            'customer_name' => 'Liam Neeson',
            'customer_phone_number' => '+1234567903',
            'car_brand_id' => $this->brand->id,
            'model' => 'Integra',
            'year' => '2033',
            'required_number_of_shops' => 2,
            'required_number_of_garages' => 1,
            'license_plate_number' => 'KLM789',
            'chassis_number' => 'CHASSIS012',
            'status' => 'pending',
        ]);

        $otherProformas = Proforma::fromOthers()->get();
        
        $this->assertFalse($otherProformas->contains($insuranceProforma));
        $this->assertTrue($otherProformas->contains($businessProforma));
    }

    /** @test */
    public function it_can_verify_proforma()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-015',
            'customer_name' => 'Maya Angelou',
            'customer_phone_number' => '+1234567904',
            'car_brand_id' => $this->brand->id,
            'model' => 'Vigor',
            'year' => '2034',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'NOP012',
            'chassis_number' => 'CHASSIS345',
            'status' => 'pending',
        ]);

        $proforma->verify();

        $this->assertEquals('completed', $proforma->fresh()->status);
        $this->assertTrue((bool) $proforma->fresh()->verified);
    }

    /** @test */
    public function it_can_check_selection_status()
    {
        $proforma = Proforma::create([
            'poster_id' => $this->insurance->id,
            'file_number' => 'TEST-016',
            'customer_name' => 'Nina Simone',
            'customer_phone_number' => '+1234567905',
            'car_brand_id' => $this->brand->id,
            'model' => 'Prelude',
            'year' => '2035',
            'required_number_of_shops' => 3,
            'required_number_of_garages' => 2,
            'license_plate_number' => 'QRS345',
            'chassis_number' => 'CHASSIS678',
            'status' => 'pending',
        ]);

        $this->assertFalse($proforma->selected());
        $this->assertNull($proforma->selectedBy());
    }
}
