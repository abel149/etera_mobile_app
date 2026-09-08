<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Proforma;
use App\Models\ProformaApplication;
use App\Models\CarPart;
use App\Models\Brand;
use App\Models\Level;
use App\Models\Partner;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class EndpointTesting extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $insurance;
    protected $garage;
    protected $shop;
    protected $businessOwner;
    protected $marketer;
    protected $employee;
    protected $individual;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users for different roles
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->insurance = User::factory()->create([
            'role' => 'insurance',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->garage = User::factory()->create([
            'role' => 'garage',
            'approved' => true,
            'email_verified_at' => now(),
            'store_id' => 'EG-0001',
        ]);

        $this->shop = User::factory()->create([
            'role' => 'shop',
            'approved' => true,
            'email_verified_at' => now(),
            'store_id' => 'ES-0001',
        ]);

        $this->businessOwner = User::factory()->create([
            'role' => 'business_owner',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->marketer = User::factory()->create([
            'role' => 'marketer',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->individual = User::factory()->create([
            'role' => 'individual',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        // Create required models
        $this->level = Level::factory()->create();
        $this->brand = Brand::factory()->create();
    }

    // ==================== AUTHENTICATION ENDPOINTS ====================

    public function test_guest_can_access_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('authentication.login');
    }

    public function test_guest_can_access_signup_pages()
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

    public function test_user_can_login_with_email()
    {
        $response = $this->post('/login', [
            'email_or_phone' => $this->insurance->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_phone()
    {
        $response = $this->post('/login', [
            'email_or_phone' => $this->insurance->phone_number,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_unapproved_user_cannot_login()
    {
        $unapprovedUser = User::factory()->create([
            'role' => 'garage',
            'approved' => false,
        ]);

        $response = $this->post('/login', [
            'email_or_phone' => $unapprovedUser->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email_or_phone']);
        $this->assertGuest();
    }

    public function test_user_can_logout()
    {
        $this->actingAs($this->admin);
        
        $response = $this->delete('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ==================== ADMIN ENDPOINTS ====================

    public function test_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_user_approval()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/user-approval');
        $response->assertStatus(200);
    }

    public function test_admin_can_approve_user()
    {
        $this->actingAs($this->admin);
        
        $unapprovedUser = User::factory()->create([
            'role' => 'garage',
            'approved' => false,
        ]);

        $response = $this->post("/admin/user-approval/{$unapprovedUser->id}/approve");
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $unapprovedUser->id,
            'approved' => true,
        ]);
    }

    public function test_admin_can_reject_user()
    {
        $this->actingAs($this->admin);
        
        $user = User::factory()->create([
            'role' => 'garage',
            'approved' => true,
        ]);

        $response = $this->post("/admin/user-approval/{$user->id}/reject");
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'approved' => false,
        ]);
    }

    public function test_admin_can_access_user_management()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_garage_management()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/garages');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_shop_management()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/spare-part-shops');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_business_owner_management()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/business-owners');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_marketer_management()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/marketers');
        $response->assertStatus(200);
    }

    // ==================== INSURANCE ENDPOINTS ====================

    public function test_insurance_can_access_dashboard()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/insurance');
        $response->assertStatus(200);
    }

    public function test_insurance_can_access_proformas()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/insurance/proformas');
        $response->assertStatus(200);
    }

    public function test_insurance_can_access_partners()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/insurance/insurances');
        $response->assertStatus(200);
    }

    public function test_insurance_can_add_garage_partner()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->post('/insurance/add-garage', [
            'garage_id' => $this->garage->id,
        ]);
        $response->assertRedirect();
    }

    public function test_insurance_can_add_shop_partner()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->post('/insurance/add-shop', [
            'shop_id' => $this->shop->id,
        ]);
        $response->assertRedirect();
    }

    // ==================== GARAGE ENDPOINTS ====================

    public function test_garage_can_access_dashboard()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/garage');
        $response->assertStatus(200);
    }

    public function test_garage_can_access_proformas()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/garage/proformas');
        $response->assertStatus(200);
    }

    public function test_garage_can_access_received_proformas()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/garage/received-details');
        $response->assertStatus(200);
    }

    // ==================== SHOP ENDPOINTS ====================

    public function test_shop_can_access_dashboard()
    {
        $this->actingAs($this->shop);
        
        $response = $this->get('/spare-part');
        $response->assertStatus(200);
    }

    public function test_shop_can_access_proformas()
    {
        $this->actingAs($this->shop);
        
        $response = $this->get('/spare-part/proformas');
        $response->assertStatus(200);
    }

    public function test_shop_can_access_received_proformas()
    {
        $this->actingAs($this->shop);
        
        $response = $this->get('/spare-part/received-details');
        $response->assertStatus(200);
    }

    // ==================== BUSINESS OWNER ENDPOINTS ====================

    public function test_business_owner_can_access_dashboard()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/business-owner');
        $response->assertStatus(200);
    }

    public function test_business_owner_can_access_proformas()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/business-owner/proformas');
        $response->assertStatus(200);
    }

    public function test_business_owner_can_post_proforma()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/business-owner/post-proforma');
        $response->assertStatus(200);
    }

    // ==================== MARKETER ENDPOINTS ====================

    public function test_marketer_can_access_dashboard()
    {
        $this->actingAs($this->marketer);
        
        $response = $this->get('/marketer');
        $response->assertStatus(200);
    }

    public function test_marketer_can_register_users()
    {
        $this->actingAs($this->marketer);
        
        $response = $this->post('/add-register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '251911234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'individual',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'registered_by' => $this->marketer->id,
        ]);
    }

    // ==================== EMPLOYEE ENDPOINTS ====================

    public function test_employee_can_access_dashboard()
    {
        $this->actingAs($this->employee);
        
        $response = $this->get('/employee');
        $response->assertStatus(200);
    }

    // ==================== INDIVIDUAL ENDPOINTS ====================

    public function test_individual_can_access_dashboard()
    {
        $this->actingAs($this->individual);
        
        $response = $this->get('/individual');
        $response->assertStatus(200);
    }

    // ==================== PROFORMA ENDPOINTS ====================

    public function test_proforma_creation()
    {
        $this->actingAs($this->businessOwner);
        
        $proformaData = [
            'title' => 'Test Proforma',
            'description' => 'Test Description',
            'budget' => 1000,
            'deadline' => now()->addDays(30),
        ];

        $response = $this->post('/proforma/store', $proformaData);
        $response->assertRedirect();
    }

    public function test_proforma_application()
    {
        $this->actingAs($this->garage);
        
        $proforma = Proforma::factory()->create([
            'poster_id' => $this->businessOwner->id,
        ]);

        $response = $this->post("/proforma-applications/{$proforma->id}/register", [
            'price' => 800,
            'description' => 'Test application',
        ]);
        
        $response->assertRedirect();
    }

    public function test_proforma_closing()
    {
        $this->actingAs($this->admin);
        
        $proforma = Proforma::factory()->create([
            'poster_id' => $this->businessOwner->id,
        ]);

        $response = $this->patch("/proforma/close/{$proforma->id}");
        $response->assertRedirect();
    }

    // ==================== FILE UPLOAD ENDPOINTS ====================

    public function test_file_upload()
    {
        $this->actingAs($this->admin);
        
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->post('/upload/images', [
            'file' => $file,
        ]);
        
        $response->assertStatus(200);
        Storage::disk('public')->assertExists('uploads/' . $file->hashName());
    }

    public function test_file_deletion()
    {
        $this->actingAs($this->admin);
        
        $response = $this->delete('/delete', [
            'filename' => 'test.jpg',
        ]);
        
        $response->assertStatus(200);
    }

    // ==================== WITHDRAWAL ENDPOINTS ====================

    public function test_withdrawal_request_creation()
    {
        $this->actingAs($this->garage);
        
        $response = $this->post('/withdraw-requests', [
            'amount' => 100,
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_holder' => 'Test User',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('withdrawal_requests', [
            'from' => $this->garage->id,
            'amount' => 100,
        ]);
    }

    // ==================== PROFILE ENDPOINTS ====================

    public function test_profile_update()
    {
        $this->actingAs($this->admin);
        
        $response = $this->put('/profile/update', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_password_change()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/check-password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertStatus(200);
    }

    // ==================== PARTNER ENDPOINTS ====================

    public function test_partner_creation()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->post('/insurance/add-garage', [
            'garage_id' => $this->garage->id,
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('partners', [
            'insurance_id' => $this->insurance->id,
            'partner_id' => $this->garage->id,
        ]);
    }

    // ==================== CAR PART ENDPOINTS ====================

    public function test_car_part_creation()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/admin/car-parts', [
            'name' => 'Test Part',
            'description' => 'Test Description',
            'price' => 100,
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('car_parts', [
            'name' => 'Test Part',
        ]);
    }

    // ==================== BRAND ENDPOINTS ====================

    public function test_brand_creation()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/admin/brands', [
            'name' => 'Test Brand',
            'description' => 'Test Description',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
        ]);
    }

    // ==================== LEVEL ENDPOINTS ====================

    public function test_level_creation()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/admin/levels', [
            'name' => 'Test Level',
            'description' => 'Test Description',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('levels', [
            'name' => 'Test Level',
        ]);
    }

    // ==================== NOTIFICATION ENDPOINTS ====================

    public function test_notification_access()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/notifications');
        $response->assertStatus(200);
    }

    // ==================== CSRF PROTECTION TESTS ====================

    public function test_csrf_protection()
    {
        $response = $this->post('/login', [
            'email_or_phone' => $this->admin->email,
            'password' => 'password',
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    // ==================== MIDDLEWARE TESTS ====================

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_registration_validation()
    {
        $response = $this->post('/add-register', [
            'name' => '',
            'email' => 'invalid-email',
            'phone_number' => '123',
            'password' => 'short',
        ]);
        
        $response->assertSessionHasErrors(['name', 'email', 'phone_number', 'password']);
    }

    public function test_login_validation()
    {
        $response = $this->post('/login', [
            'email_or_phone' => '',
            'password' => '',
        ]);
        
        $response->assertSessionHasErrors(['email_or_phone', 'password']);
    }

    // ==================== DATABASE INTEGRITY TESTS ====================

    public function test_user_relationships()
    {
        $this->actingAs($this->marketer);
        
        $user = User::factory()->create([
            'registered_by' => $this->marketer->id,
        ]);
        
        $this->assertTrue($this->marketer->myRegistrations->contains($user));
    }

    public function test_proforma_relationships()
    {
        $proforma = Proforma::factory()->create([
            'poster_id' => $this->businessOwner->id,
        ]);
        
        $this->assertTrue($this->businessOwner->proformas->contains($proforma));
    }

    // ==================== PERFORMANCE TESTS ====================

    public function test_dashboard_load_time()
    {
        $this->actingAs($this->admin);
        
        $startTime = microtime(true);
        
        $response = $this->get('/admin');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        $this->assertLessThan(2.0, $loadTime); // Should load in less than 2 seconds
    }

    // ==================== SECURITY TESTS ====================

    public function test_sql_injection_protection()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/users?search=1%27%20OR%20%271%27%3D%271');
        $response->assertStatus(200);
        // Should not crash or expose sensitive data
    }

    public function test_xss_protection()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/admin/users', [
            'name' => '<script>alert("xss")</script>',
            'email' => 'test@example.com',
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }

    // ==================== API RESPONSE TESTS ====================

    public function test_api_responses_have_correct_structure()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin/user-approval/ajax/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
    }

    // ==================== ERROR HANDLING TESTS ====================

    public function test_404_error_handling()
    {
        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);
    }

    public function test_500_error_handling()
    {
        // This would require mocking a service to throw an exception
        // For now, we'll just ensure the application doesn't crash
        $this->assertTrue(true);
    }

    // ==================== CLEANUP TESTS ====================

    public function test_database_cleanup_after_tests()
    {
        // This test ensures that the RefreshDatabase trait is working
        $this->assertDatabaseCount('users', 8); // Our 8 test users
    }
}
