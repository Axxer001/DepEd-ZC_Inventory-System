<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    public function test_admin_can_view_asset_profile_with_new_fields()
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);

        $class = \App\Models\Classification::firstOrCreate(['name' => 'IT Equipment']);
        $cat = \App\Models\Category::firstOrCreate(['name' => 'Laptop'], ['classification_id' => $class->id]);
        $item = \App\Models\Item::firstOrCreate(['name' => 'Acer TravelMate'], ['category_id' => $cat->id]);
        
        $acq = \App\Models\AcquisitionSource::create([
            'name' => 'DepEd Central Office',
            'source_type' => 'Internal',
            'contact_person' => 'Juan Dela Cruz',
            'contact_position' => 'Chief Officer'
        ]);

        $supplier = DB::table('suppliers')->insertGetId([
            'name' => 'Phonepatch Marketing',
            'service_center' => 'ZC Service Center',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $source = \App\Models\AssetSource::create([
            'item_id' => $item->id,
            'acquisition_source_id' => $acq->id,
            'supplier_id' => $supplier,
            'asset_cost' => 50000,
            'quantity' => 1,
            'condition' => 'Good Condition',
            'acceptance_date' => now()->toDateString()
        ]);

        $assignmentId = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source->id,
            'acquisition_cost' => 50000,
            'acquisition_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($admin)->get("/assets/{$assignmentId}/profile");
        $response->assertStatus(200);
        $response->assertSee('Juan Dela Cruz');
        $response->assertSee('ZC Service Center');
    }

    public function test_editing_acquisition_source_contact_does_not_overwrite_existing_asset_source_contact()
    {
        $admin = User::factory()->create(['role' => 'admin', 'approved' => true]);

        $class = \App\Models\Classification::firstOrCreate(['name' => 'IT Equipment']);
        $cat = \App\Models\Category::firstOrCreate(['name' => 'Laptop'], ['classification_id' => $class->id]);
        $item = \App\Models\Item::firstOrCreate(['name' => 'Acer TravelMate'], ['category_id' => $cat->id]);
        
        $acq = \App\Models\AcquisitionSource::create([
            'name' => 'DepEd Central Office',
            'source_type' => 'Internal',
            'contact_person' => 'Contact Person 1',
            'contact_position' => 'Chief Officer'
        ]);

        $supplier = DB::table('suppliers')->insertGetId([
            'name' => 'Phonepatch Marketing',
            'service_center' => 'Service Center 1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $source = \App\Models\AssetSource::create([
            'item_id' => $item->id,
            'acquisition_source_id' => $acq->id,
            'supplier_id' => $supplier,
            'asset_cost' => 50000,
            'quantity' => 1,
            'condition' => 'Good Condition',
            'acceptance_date' => now()->toDateString()
        ]);

        $assignmentId = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source->id,
            'acquisition_cost' => 50000,
            'acquisition_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Change the main registry values
        $acq->update(['contact_person' => 'Contact Person 2']);
        DB::table('suppliers')->where('id', $supplier)->update(['service_center' => 'Service Center 2']);

        $response = $this->actingAs($admin)->get("/assets/{$assignmentId}/profile");
        $response->assertStatus(200);
        // The page should still display the old historical values
        $response->assertSee('Contact Person 1');
        $response->assertSee('Service Center 1');
        $response->assertDontSee('Contact Person 2');
        $response->assertDontSee('Service Center 2');
    }
}
