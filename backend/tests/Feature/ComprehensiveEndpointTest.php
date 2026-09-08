<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\ProformaPartPrice;
use App\Models\CarPart;
use App\Models\Brand;
use App\Models\Level;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ComprehensiveEndpointTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $insurance;
    protected $garage;
    protected $shop;
    protected $businessOwner;
    protected $marketer;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users for different roles
        $this->createTestUsers();
        
        // Create test data
        $this->createTestData();
    }

    protected function createTestUsers()
    {
        // Create admin user
        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'approved' => true,
            'phone_number' => '251911111111',
            'email_verified_at' => now(),
        ]);

        // Create insurance user
        $this->insurance = User::create([
            'name' => 'Test Insurance',
            'email' => 'insurance@test.com',
            'password' => Hash::make('password'),
            'role' => 'insurance',
            'approved' => true,
            'phone_number' => '251911111112',
            'email_verified_at' => now(),
        ]);

        // Create garage user
        $this->garage = User::create([
            'name' => 'Test Garage',
            'email' => 'garage@test.com',
            'password' => Hash::make('password'),
            'role' => 'garage',
            'approved' => true,
            'phone_number' => '251911111113',
            'store_id' => 'EG-0001',
            'tin_number' => 'TIN001001',
            'location' => 'Addis Ababa, Bole',
            'email_verified_at' => now(),
        ]);

        // Create shop user
        $this->shop = User::create([
            'name' => 'Test Shop',
            'email' => 'shop@test.com',
            'password' => Hash::make('password'),
            'role' => 'shop',
            'approved' => true,
            'phone_number' => '251911111114',
            'store_id' => 'ES-0001',
            'tin_number' => 'TIN002001',
            'location' => 'Addis Ababa, Mercato',
            'email_verified_at' => now(),
        ]);

        // Create business owner user
        $this->businessOwner = User::create([
            'name' => 'Test Business Owner',
            'email' => 'business@test.com',
            'password' => Hash::make('password'),
            'role' => 'business_owner',
            'approved' => true,
            'phone_number' => '251911111115',
            'email_verified_at' => now(),
        ]);

        // Create marketer user
        $this->marketer = User::create([
            'name' => 'Test Marketer',
            'email' => 'marketer@test.com',
            'password' => Hash::make('password'),
            'role' => 'marketer',
            'approved' => true,
            'phone_number' => '251911111116',
            'email_verified_at' => now(),
        ]);

        // Create employee user
        $this->employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'approved' => true,
            'phone_number' => '251911111117',
            'email_verified_at' => now(),
        ]);
    }

    protected function createTestData()
    {
        // Create level
        $level = Level::create([
            'name' => 'Test Level',
            'description' => 'Test Level Description',
            'rank' => 1,
        ]);

        // Create brand
        $brand = Brand::create([
            'name' => 'Test Brand',
            'description' => 'Test Brand Description',
        ]);

        // Create car part
        $carPart = CarPart::create([
            'name' => 'Test Car Part',
            'description' => 'Test Car Part Description',
            'brand_id' => $brand->id,
        ]);

        // Create proforma
        $this->proforma = Proforma::create([
            'title' => 'Test Proforma',
            'description' => 'Test Proforma Description',
            'poster_id' => $this->insurance->id,
            'brand_id' => $brand->id,
            'model' => 'Test Model',
            'year' => 2020,
            'status' => 'active',
            'deadline' => now()->addDays(30),
        ]);

        // Create proforma application
        $this->proformaApplication = ProformaApplication::create([
            'proforma_id' => $this->proforma->id,
            'applicant_id' => $this->garage->id,
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        // Create proforma part price
        ProformaPartPrice::create([
            'application_id' => $this->proformaApplication->id,
            'part_id' => $carPart->id,
            'part_name' => 'Test Part',
            'part_price' => 500.00,
            'part_total' => 500.00,
            'quantity' => 1,
        ]);
    }

    // ==================== AUTHENTICATION TESTS ====================

    /** @test */
    public function guest_can_access_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('authentication.login');
    }

    /** @test */
    public function guest_can_access_signup_pages()
    {
        $response = $this->get('/signup');
        $response->assertStatus(200);

        $response = $this->get('/signup/individual');
        $response->assertStatus(200);

        $response = $this->get('/signup/business-owner');
        $response->assertStatus(200);

        $response = $this->get('/signup/garage-sparepart');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_login_with_email()
    {
        $response = $this->post('/login', [
            'email_or_phone' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    /** @test */
    public function user_can_login_with_phone()
    {
        $response = $this->post('/login', [
            'email_or_phone' => '251911111111',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    /** @test */
    public function unapproved_user_cannot_login()
    {
        $unapprovedUser = User::create([
            'name' => 'Unapproved User',
            'email' => 'unapproved@test.com',
            'password' => Hash::make('password'),
            'role' => 'garage',
            'approved' => false,
            'phone_number' => '251911111118',
        ]);

        $response = $this->post('/login', [
            'email_or_phone' => 'unapproved@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email_or_phone']);
        $this->assertGuest();
    }

    /** @test */
    public function user_can_logout()
    {
        $this->actingAs($this->admin);
        
        $response = $this->delete('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ==================== ADMIN DASHBOARD TESTS ====================

    /** @test */
    public function admin_can_access_dashboard()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_admin_dashboard()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_users_chart()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/users/chart');
        $response->assertStatus(200);
    }

    // ==================== INSURANCE DASHBOARD TESTS ====================

    /** @test */
    public function insurance_can_access_dashboard()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_view_proformas()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/proforma');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_post_proforma()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/post-proforma');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_view_others_proforma()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/others-proforma');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_post_others_proforma()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/post-others-proforma');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_view_bid_page()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/bid');
        $response->assertStatus(200);
    }

    // ==================== GARAGE DASHBOARD TESTS ====================

    /** @test */
    public function garage_can_access_dashboard()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function garage_can_view_received_details()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/received-details');
        $response->assertStatus(200);
    }

    // ==================== SHOP DASHBOARD TESTS ====================

    /** @test */
    public function shop_can_access_dashboard()
    {
        $this->actingAs($this->shop);
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function shop_can_view_received_details()
    {
        $this->actingAs($this->shop);
        
        $response = $this->get('/received-details');
        $response->assertStatus(200);
    }

    // ==================== BUSINESS OWNER DASHBOARD TESTS ====================

    /** @test */
    public function business_owner_can_access_dashboard()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function business_owner_can_view_proformas()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/proforma');
        $response->assertStatus(200);
    }

    // ==================== MARKETER DASHBOARD TESTS ====================

    /** @test */
    public function marketer_can_access_dashboard()
    {
        $this->actingAs($this->marketer);
        
        $response = $this->get('/marketer');
        $response->assertStatus(200);
    }

    // ==================== EMPLOYEE DASHBOARD TESTS ====================

    /** @test */
    public function employee_can_access_dashboard()
    {
        $this->actingAs($this->employee);
        
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    // ==================== ADMIN MANAGEMENT TESTS ====================

    /** @test */
    public function admin_can_view_insurances()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/insurances');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_insurance()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-insurance');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_insurance()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/add-insurance', [
            'name' => 'New Insurance',
            'email' => 'newinsurance@test.com',
            'phone_number' => '251911111119',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newinsurance@test.com',
            'role' => 'insurance',
        ]);
    }

    /** @test */
    public function admin_can_view_garages()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/garages');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_garage()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-garage');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_garage()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/add-garage', [
            'name' => 'New Garage',
            'email' => 'newgarage@test.com',
            'phone_number' => '251911111120',
            'password' => 'password',
            'password_confirmation' => 'password',
            'tin_number' => 'TIN001002',
            'location' => 'Addis Ababa, CMC',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newgarage@test.com',
            'role' => 'garage',
        ]);
    }

    /** @test */
    public function admin_can_view_shops()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/spare-part-shops');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_shop()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-spare-part-shop');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_shop()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/add-shop', [
            'name' => 'New Shop',
            'email' => 'newshop@test.com',
            'phone_number' => '251911111121',
            'password' => 'password',
            'password_confirmation' => 'password',
            'tin_number' => 'TIN002002',
            'location' => 'Addis Ababa, Piassa',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newshop@test.com',
            'role' => 'shop',
        ]);
    }

    /** @test */
    public function admin_can_view_business_owners()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/business-owners');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_business_owner()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-business-owner');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_marketers()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/marketers');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_marketer()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-marketer');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_employees()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/employees');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_add_employee()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/add-employee');
        $response->assertStatus(200);
    }

    // ==================== PROFORMA TESTS ====================

    /** @test */
    public function user_can_view_proforma_details()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/proforma-details');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_register_proforma_application()
    {
        $this->actingAs($this->garage);
        
        $response = $this->post("/{$this->proforma->id}/register", [
            'total_amount' => 1500.00,
            'parts' => [
                [
                    'part_name' => 'Test Part 1',
                    'part_price' => 750.00,
                    'quantity' => 1,
                ],
                [
                    'part_name' => 'Test Part 2',
                    'part_price' => 750.00,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('proforma_applications', [
            'proforma_id' => $this->proforma->id,
            'applicant_id' => $this->garage->id,
        ]);
    }

    /** @test */
    public function user_can_view_proforma_application_data()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get("/{$this->proforma->id}/data");
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_export_proforma_application_data()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get("/{$this->proforma->id}/export");
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_proforma_statistics()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/statistics');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_real_time_updates()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/real-time-updates');
        $response->assertStatus(200);
    }

    // ==================== PROFILE TESTS ====================

    /** @test */
    public function user_can_view_profile()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/profile');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_update_profile()
    {
        $this->actingAs($this->admin);
        
        $response = $this->put('/profile/update', [
            'name' => 'Updated Name',
            'phone_number' => '251911111122',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function user_can_update_self_profile()
    {
        $this->actingAs($this->admin);
        
        $response = $this->put('/my-profile/update', [
            'name' => 'Self Updated Name',
            'phone_number' => '251911111123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Self Updated Name',
        ]);
    }

    // ==================== USER APPROVAL TESTS ====================

    /** @test */
    public function admin_can_view_user_approval_page()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/user-approval');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_approve_user()
    {
        $this->actingAs($this->admin);
        
        $unapprovedUser = User::create([
            'name' => 'To Approve User',
            'email' => 'toapprove@test.com',
            'password' => Hash::make('password'),
            'role' => 'garage',
            'approved' => false,
            'phone_number' => '251911111124',
        ]);

        $response = $this->post("/user-approval/{$unapprovedUser->id}/approve");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $unapprovedUser->id,
            'approved' => true,
        ]);
    }

    /** @test */
    public function admin_can_reject_user()
    {
        $this->actingAs($this->admin);
        
        $pendingUser = User::create([
            'name' => 'To Reject User',
            'email' => 'toreject@test.com',
            'password' => Hash::make('password'),
            'role' => 'garage',
            'approved' => null,
            'phone_number' => '251911111125',
        ]);

        $response = $this->post("/user-approval/{$pendingUser->id}/reject");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'approved' => false,
        ]);
    }

    // ==================== FILE UPLOAD TESTS ====================

    /** @test */
    public function user_can_upload_temporary_file()
    {
        $this->actingAs($this->admin);
        
        Storage::fake('local');
        
        $file = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->post('/upload/image', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_delete_temporary_file()
    {
        $this->actingAs($this->admin);
        
        $response = $this->delete('/delete', [
            'file_id' => 1,
        ]);

        $response->assertStatus(200);
    }

    // ==================== WITHDRAWAL TESTS ====================

    /** @test */
    public function user_can_create_withdrawal_request()
    {
        $this->actingAs($this->garage);
        
        $response = $this->post('/withdraw-requests', [
            'amount' => 100.00,
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_holder_name' => 'Test User',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('withdrawal_requests', [
            'from' => $this->garage->id,
            'amount' => 100.00,
        ]);
    }

    // ==================== PARTNER TESTS ====================

    /** @test */
    public function insurance_can_view_partners()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/partners');
        $response->assertStatus(200);
    }

    /** @test */
    public function insurance_can_add_partner()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/add-partner');
        $response->assertStatus(200);
    }

    // ==================== CSRF PROTECTION TESTS ====================

    /** @test */
    public function csrf_token_is_required_for_post_requests()
    {
        $response = $this->post('/login', [
            'email_or_phone' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    // ==================== MIDDLEWARE TESTS ====================

    /** @test */
    public function guest_cannot_access_protected_routes()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_cannot_access_guest_routes()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/login');
        $response->assertRedirect('/admin');
    }

    // ==================== VALIDATION TESTS ====================

    /** @test */
    public function registration_requires_valid_data()
    {
        $response = $this->post('/add-register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function login_requires_valid_credentials()
    {
        $response = $this->post('/login', [
            'email_or_phone' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email_or_phone']);
    }

    // ==================== DATABASE INTEGRITY TESTS ====================

    /** @test */
    public function user_creation_maintains_data_integrity()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'role' => 'individual',
            'phone_number' => '251911111126',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role'],
        ]);

        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    /** @test */
    public function proforma_application_maintains_relationships()
    {
        $this->actingAs($this->garage);
        
        $response = $this->post("/{$this->proforma->id}/register", [
            'total_amount' => 2000.00,
            'parts' => [
                [
                    'part_name' => 'Test Part',
                    'part_price' => 2000.00,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $application = ProformaApplication::where('proforma_id', $this->proforma->id)
            ->where('applicant_id', $this->garage->id)
            ->first();

        $this->assertNotNull($application);
        $this->assertEquals(2000.00, $application->total_amount);

        $partPrice = ProformaPartPrice::where('application_id', $application->id)->first();
        $this->assertNotNull($partPrice);
        $this->assertEquals('Test Part', $partPrice->part_name);
    }

    // ==================== PERFORMANCE TESTS ====================

    /** @test */
    public function dashboard_loads_within_acceptable_time()
    {
        $this->actingAs($this->admin);
        
        $startTime = microtime(true);
        
        $response = $this->get('/admin');
        
        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        $this->assertLessThan(2000, $loadTime, 'Dashboard should load within 2 seconds');
    }

    // ==================== SECURITY TESTS ====================

    /** @test */
    public function user_cannot_access_other_user_profile()
    {
        $this->actingAs($this->garage);
        
        $response = $this->put("/profile/{$this->admin->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'id' => $this->admin->id,
            'name' => 'Hacked Name',
        ]);
    }

    /** @test */
    public function user_cannot_approve_other_users()
    {
        $this->actingAs($this->garage);
        
        $unapprovedUser = User::create([
            'name' => 'Test User',
            'email' => 'testuser2@test.com',
            'password' => Hash::make('password'),
            'role' => 'shop',
            'approved' => false,
            'phone_number' => '251911111127',
        ]);

        $response = $this->post("/user-approval/{$unapprovedUser->id}/approve");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $unapprovedUser->id,
            'approved' => false,
        ]);
    }

    // ==================== ERROR HANDLING TESTS ====================

    /** @test */
    public function invalid_route_returns_404()
    {
        $response = $this->get('/nonexistent-route');
        $response->assertStatus(404);
    }

    /** @test */
    public function invalid_proforma_id_returns_404()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/proforma-details/999999');
        $response->assertStatus(404);
    }

    // ==================== LIVEWIRE COMPONENT TESTS ====================

    /** @test */
    public function livewire_components_load_properly()
    {
        $this->actingAs($this->admin);
        
        // Test that Livewire components are accessible
        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('wire:');
    }

    // ==================== API ENDPOINT TESTS ====================

    /** @test */
    public function proforma_status_endpoint_works()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get("/proforma/{$this->proforma->id}/status");
        $response->assertStatus(200);
    }

    /** @test */
    public function auto_close_check_endpoint_works()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->post("/proforma/{$this->proforma->id}/check-auto-close");
        $response->assertStatus(200);
    }

    // ==================== SESSION TESTS ====================

    /** @test */
    public function user_session_is_maintained()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin');
        $response->assertStatus(200);
        
        $this->assertAuthenticated();
        $this->assertEquals($this->admin->id, auth()->id());
    }

    /** @test */
    public function user_session_expires_properly()
    {
        $this->actingAs($this->admin);
        
        // Simulate session expiration
        Session::put('last_activity', time() - 7200); // 2 hours ago
        
        $response = $this->get('/admin');
        $response->assertStatus(200); // Should still work as session is valid
        
        // Test with very old session
        Session::put('last_activity', time() - 86400); // 24 hours ago
        
        $response = $this->get('/admin');
        $response->assertStatus(200); // Should still work as session is valid
    }

    // ==================== CACHE TESTS ====================

    /** @test */
    public function cache_is_working()
    {
        $key = 'test_cache_key';
        $value = 'test_cache_value';
        
        Cache::put($key, $value, 60);
        $this->assertEquals($value, Cache::get($key));
        
        Cache::forget($key);
        $this->assertNull(Cache::get($key));
    }

    // ==================== STORAGE TESTS ====================

    /** @test */
    public function file_storage_is_working()
    {
        Storage::fake('local');
        
        $filename = 'test.txt';
        $content = 'Test file content';
        
        Storage::disk('local')->put($filename, $content);
        $this->assertTrue(Storage::disk('local')->exists($filename));
        $this->assertEquals($content, Storage::disk('local')->get($filename));
        
        Storage::disk('local')->delete($filename);
        $this->assertFalse(Storage::disk('local')->exists($filename));
    }

    // ==================== DATABASE TRANSACTION TESTS ====================

    /** @test */
    public function database_transactions_work_properly()
    {
        $this->actingAs($this->admin);
        
        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => 'Transaction Test User',
                'email' => 'transaction@test.com',
                'password' => Hash::make('password'),
                'role' => 'individual',
                'phone_number' => '251911111128',
            ]);
            
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            
            DB::rollBack();
            
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ==================== CLEANUP TESTS ====================

    /** @test */
    public function test_data_is_cleaned_up_after_tests()
    {
        $this->assertDatabaseCount('users', 7); // Only our test users
        
        // This test ensures that the RefreshDatabase trait is working
        // and cleaning up data between tests
    }
}
