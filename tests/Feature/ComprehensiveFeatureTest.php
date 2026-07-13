<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ComprehensiveFeatureTest extends TestCase
{
    use DatabaseTransactions;

    // --- PUBLIC VIEW TESTS ---

    public function test_user_can_view_login_page()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_user_can_register()
    {
        $response = $this->withSession(['otp_verified_email' => 'john@example.com'])
            ->post('/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'system_type' => 'main',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);
        
        $this->assertDatabaseHas('pending_registrations', ['email' => 'john@example.com']);
        $response->assertSessionHas('success');
    }

    public function test_pending_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'password' => bcrypt('Password123!'),
            'approved' => false
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
    }

    public function test_approved_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'approved@example.com',
            'password' => bcrypt('Password123!'),
            'approved' => true
        ]);

        $response = $this->post('/login', [
            'email' => 'approved@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    // --- ADMIN TESTS ---

    public function test_admin_can_view_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_inventory_setup()
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);
        
        $response = $this->actingAs($admin)->get('/inventory-setup');
        $response->assertStatus(200);
    }

    // --- SUPER ADMIN TESTS ---

    public function test_super_admin_can_view_user_management()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'approved' => true]);
        
        $response = $this->actingAs($superAdmin)->get('/admin/user-management');
        $response->assertStatus(200);
    }

    public function test_regular_admin_cannot_view_user_management()
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);
        
        $response = $this->actingAs($admin)->get('/admin/user-management');
        $response->assertStatus(403);
    }
}
