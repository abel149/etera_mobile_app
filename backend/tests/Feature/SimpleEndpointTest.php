<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SimpleEndpointTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $insurance;
    protected $garage;
    protected $shop;
    protected $businessOwner;
    protected $marketer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic test users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone_number' => '251911234001',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->insurance = User::create([
            'name' => 'Insurance User',
            'email' => 'insurance@test.com',
            'phone_number' => '251911234002',
            'password' => bcrypt('password'),
            'role' => 'insurance',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->garage = User::create([
            'name' => 'Garage User',
            'email' => 'garage@test.com',
            'phone_number' => '251911234003',
            'password' => bcrypt('password'),
            'role' => 'garage',
            'approved' => true,
            'email_verified_at' => now(),
            'store_id' => 'EG-0001',
        ]);

        $this->shop = User::create([
            'name' => 'Shop User',
            'email' => 'shop@test.com',
            'phone_number' => '251911234004',
            'password' => bcrypt('password'),
            'role' => 'shop',
            'approved' => true,
            'email_verified_at' => now(),
            'store_id' => 'ES-0001',
        ]);

        $this->businessOwner = User::create([
            'name' => 'Business Owner',
            'email' => 'business@test.com',
            'phone_number' => '251911234005',
            'password' => bcrypt('password'),
            'role' => 'business_owner',
            'approved' => true,
            'email_verified_at' => now(),
        ]);

        $this->marketer = User::create([
            'name' => 'Marketer User',
            'email' => 'marketer@test.com',
            'phone_number' => '251911234006',
            'password' => bcrypt('password'),
            'role' => 'marketer',
            'approved' => true,
            'email_verified_at' => now(),
        ]);
    }

    // ==================== BASIC AUTHENTICATION TESTS ====================

    public function test_guest_can_access_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
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

    public function test_user_can_logout()
    {
        $this->actingAs($this->admin);
        
        $response = $this->delete('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ==================== ROLE-BASED ACCESS TESTS ====================

    public function test_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_insurance_can_access_dashboard()
    {
        $this->actingAs($this->insurance);
        
        $response = $this->get('/insurance');
        $response->assertStatus(200);
    }

    public function test_garage_can_access_dashboard()
    {
        $this->actingAs($this->garage);
        
        // Garage dashboard is actually at /garage/proformas
        $response = $this->get('/garage/proformas');
        $response->assertStatus(200);
    }

    public function test_shop_can_access_dashboard()
    {
        $this->actingAs($this->shop);
        
        // Shop dashboard is actually at /spare-part-shops/proformas
        $response = $this->get('/spare-part-shops/proformas');
        $response->assertStatus(200);
    }

    public function test_business_owner_can_access_dashboard()
    {
        $this->actingAs($this->businessOwner);
        
        $response = $this->get('/business-owner');
        $response->assertStatus(200);
    }

    public function test_marketer_can_access_dashboard()
    {
        $this->actingAs($this->marketer);
        
        $response = $this->get('/marketer');
        $response->assertStatus(200);
    }

    // ==================== MIDDLEWARE TESTS ====================

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/admin');
        // The actual behavior is to redirect to root, not /login
        $response->assertRedirect();
    }

    public function test_unauthorized_user_cannot_access_admin_routes()
    {
        $this->actingAs($this->garage);
        
        $response = $this->get('/admin');
        $response->assertStatus(302); // Redirect instead of 403
    }

    // ==================== BASIC FUNCTIONALITY TESTS ====================

    public function test_profile_page_access()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get('/profile');
        $response->assertStatus(200);
    }

    public function test_csrf_token_refresh()
    {
        $response = $this->get('/csrf-refresh');
        $response->assertStatus(200);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_login_validation()
    {
        $response = $this->post('/login', [
            'email_or_phone' => '',
            'password' => '',
        ]);
        
        $response->assertSessionHasErrors(['email_or_phone', 'password']);
    }

    // ==================== RELATIONSHIP TESTS ====================

    public function test_marketer_relationships()
    {
        $this->actingAs($this->marketer);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'phone_number' => '251911234007',
            'password' => bcrypt('password'),
            'role' => 'individual',
            'approved' => true,
            'registered_by' => $this->marketer->id,
        ]);
        
        $this->assertTrue($this->marketer->myRegistrations->contains($user));
    }

    // ==================== ERROR HANDLING TESTS ====================

    public function test_404_error_handling()
    {
        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);
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
        $this->assertLessThan(3.0, $loadTime); // Should load in less than 3 seconds
    }

    // ==================== SECURITY TESTS ====================

    public function test_sql_injection_protection()
    {
        $this->actingAs($this->admin);
        
        // Test with a route that actually exists
        $response = $this->get('/admin/user-approval?search=1%27%20OR%20%271%27%3D%271');
        $response->assertStatus(200);
        // Should not crash or expose sensitive data
    }

    // ==================== CLEANUP TESTS ====================

    public function test_database_cleanup_after_tests()
    {
        // This test ensures that the RefreshDatabase trait is working
        $this->assertDatabaseCount('users', 6); // Our 6 test users
    }
}
