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

    public function test_school_users_only_see_own_school_assets_on_supplier_and_source_profiles()
    {
        $school1 = \App\Models\School::withoutEvents(fn() => \App\Models\School::create([
            'school_id' => 'SCH-TEST-01', 'name' => 'School Alpha', 'type' => 'Elementary', 'location' => 'ZC'
        ]));
        $school2 = \App\Models\School::withoutEvents(fn() => \App\Models\School::create([
            'school_id' => 'SCH-TEST-02', 'name' => 'School Beta', 'type' => 'Elementary', 'location' => 'ZC'
        ]));

        $schoolUser1 = User::factory()->create(['system_type' => 'school', 'school_id' => $school1->id, 'role' => 'admin', 'approved' => true]);
        $schoolUser2 = User::factory()->create(['system_type' => 'school', 'school_id' => $school2->id, 'role' => 'admin', 'approved' => true]);
        $mainAdmin = User::factory()->create(['system_type' => 'main', 'role' => 'admin', 'approved' => true]);

        $class = \App\Models\Classification::firstOrCreate(['name' => 'IT Equipment']);
        $cat = \App\Models\Category::firstOrCreate(['name' => 'Laptop'], ['classification_id' => $class->id]);
        $item = \App\Models\Item::firstOrCreate(['name' => 'Acer TravelMate'], ['category_id' => $cat->id]);

        $acq = \App\Models\AcquisitionSource::create([
            'name' => 'Test Source Scoped', 'source_type' => 'Internal', 'contact_person' => 'Supplier Guy'
        ]);

        $supplier = DB::table('suppliers')->insertGetId([
            'name' => 'Test Supplier Scoped', 'service_center' => 'ZC Service Center', 'created_at' => now(), 'updated_at' => now()
        ]);

        // Create 2 assets: one assigned to School 1, one assigned to School 2
        $source1 = \App\Models\AssetSource::create([
            'item_id' => $item->id, 'acquisition_source_id' => $acq->id, 'supplier_id' => $supplier,
            'asset_cost' => 10000, 'quantity' => 1, 'condition' => 'Good Condition', 'acceptance_date' => now()->toDateString()
        ]);

        $source2 = \App\Models\AssetSource::create([
            'item_id' => $item->id, 'acquisition_source_id' => $acq->id, 'supplier_id' => $supplier,
            'asset_cost' => 20000, 'quantity' => 1, 'condition' => 'Good Condition', 'acceptance_date' => now()->toDateString()
        ]);

        $assign1 = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source1->id, 'school_id' => $school1->id, 'acquisition_cost' => 10000,
            'acquisition_date' => now()->toDateString(), 'property_number' => 'PROP-SCH1', 'created_at' => now(), 'updated_at' => now()
        ]);

        $assign2 = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source2->id, 'school_id' => $school2->id, 'acquisition_cost' => 20000,
            'acquisition_date' => now()->toDateString(), 'property_number' => 'PROP-SCH2', 'created_at' => now(), 'updated_at' => now()
        ]);

        // --- SUPPLIER SCOPING TEST ---
        // School User 1 sees School 1 asset, not School 2
        $response1 = $this->actingAs($schoolUser1)->get("/admin/suppliers/{$supplier}");
        $response1->assertStatus(200);
        $response1->assertSee('PROP-SCH1');
        $response1->assertDontSee('PROP-SCH2');
        $this->assertEquals(1, $response1->viewData('stats')->total_assets);
        $this->assertEquals(10000, $response1->viewData('stats')->total_value);

        // School User 2 sees School 2 asset, not School 1
        $response2 = $this->actingAs($schoolUser2)->get("/admin/suppliers/{$supplier}");
        $response2->assertStatus(200);
        $response2->assertSee('PROP-SCH2');
        $response2->assertDontSee('PROP-SCH1');
        $this->assertEquals(1, $response2->viewData('stats')->total_assets);
        $this->assertEquals(20000, $response2->viewData('stats')->total_value);

        // Main Admin sees both
        $responseMain = $this->actingAs($mainAdmin)->get("/admin/suppliers/{$supplier}");
        $responseMain->assertStatus(200);
        $responseMain->assertSee('PROP-SCH1');
        $responseMain->assertSee('PROP-SCH2');
        $this->assertEquals(2, $responseMain->viewData('stats')->total_assets);
        $this->assertEquals(30000, $responseMain->viewData('stats')->total_value);

        // --- SOURCE SCOPING TEST ---
        // School User 1 sees School 1 asset, not School 2
        $sResponse1 = $this->actingAs($schoolUser1)->get("/admin/sources/{$acq->id}");
        $sResponse1->assertStatus(200);
        $sResponse1->assertSee('PROP-SCH1');
        $sResponse1->assertDontSee('PROP-SCH2');
        $this->assertEquals(1, $sResponse1->viewData('stats')->total_assets);
        $this->assertEquals(10000, $sResponse1->viewData('stats')->total_value);

        // School User 2 sees School 2 asset, not School 1
        $sResponse2 = $this->actingAs($schoolUser2)->get("/admin/sources/{$acq->id}");
        $sResponse2->assertStatus(200);
        $sResponse2->assertSee('PROP-SCH2');
        $sResponse2->assertDontSee('PROP-SCH1');
        $this->assertEquals(1, $sResponse2->viewData('stats')->total_assets);
        $this->assertEquals(20000, $sResponse2->viewData('stats')->total_value);

        // Main Admin sees both
        $sResponseMain = $this->actingAs($mainAdmin)->get("/admin/sources/{$acq->id}");
        $sResponseMain->assertStatus(200);
        $sResponseMain->assertSee('PROP-SCH1');
        $sResponseMain->assertSee('PROP-SCH2');
        $this->assertEquals(2, $sResponseMain->viewData('stats')->total_assets);
        $this->assertEquals(30000, $sResponseMain->viewData('stats')->total_value);
    }

    public function test_admin_can_update_asset_specifications()
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

        $newCategory = \App\Models\Category::firstOrCreate(['name' => 'Desktop'], ['classification_id' => $class->id]);

        $response = $this->actingAs($admin)->post("/assets/{$assignmentId}/update", [
            'item_name' => 'Acer TravelMate V2',
            'description' => 'Updated TravelMate Laptop',
            'condition' => 'Needs Repair',
            'property_number' => 'PROP-NEW-123',
            'serial_number' => 'SN-NEW-123',
            'asset_cost' => 60000,
            'quantity' => 2,
            'acquisition_date' => now()->subDay()->toDateString(),
            'category_id' => $newCategory->id,
            'acquisition_source_id' => $acq->id,
            'procurement_mode_id' => null,
            'supplier_id' => $supplier,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        // Verify database updates
        $updatedAssignment = DB::table('asset_assignments')->where('id', $assignmentId)->first();
        $this->assertEquals('PROP-NEW-123', $updatedAssignment->property_number);
        $this->assertEquals('SN-NEW-123', $updatedAssignment->serial_number);
        $this->assertEquals(120000, $updatedAssignment->acquisition_cost);

        $updatedSource = DB::table('asset_sources')->where('id', $source->id)->first();
        $this->assertEquals('Updated TravelMate Laptop', $updatedSource->description);
        $this->assertEquals('Needs Repair', $updatedSource->condition);
        $this->assertEquals(60000, $updatedSource->asset_cost);
        $this->assertEquals(2, $updatedSource->quantity);

        // Verify new item created/selected
        $updatedItem = DB::table('items')->where('id', $updatedSource->item_id)->first();
        $this->assertEquals('ACER TRAVELMATE V2', $updatedItem->name);
        $this->assertEquals($newCategory->id, $updatedItem->category_id);
    }
}
