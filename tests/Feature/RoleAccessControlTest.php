<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Employee;
use App\Models\Classification;
use App\Models\Category;
use App\Models\Item;
use App\Models\AcquisitionSource;
use App\Models\AssetSource;
use Illuminate\Support\Facades\DB;

class RoleAccessControlTest extends TestCase
{
    use DatabaseTransactions;

    // --- REQUIREMENT 1: Suppliers & Sources (Allow Admin(Main)) ---

    public function test_main_admin_can_create_source_and_supplier()
    {
        $mainAdmin = User::factory()->create([
            'system_type' => 'main',
            'role' => 'admin',
            'approved' => true
        ]);

        $response = $this->actingAs($mainAdmin)->post('/admin/sources', [
            'name' => 'Main Admin Source',
            'source_type' => 'Internal',
            'contact_person' => 'John Source',
            'contact_position' => 'Manager'
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('acquisition_sources', ['name' => 'Main Admin Source']);

        $response = $this->actingAs($mainAdmin)->post('/admin/suppliers', [
            'name' => 'Main Admin Supplier',
            'service_center' => 'ZC Center'
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('suppliers', ['name' => 'Main Admin Supplier']);
    }

    public function test_school_admin_cannot_create_source_and_supplier()
    {
        $school = School::create(['school_id' => 'SCH-001', 'name' => 'Test School', 'type' => 'Elementary', 'location' => 'ZC']);
        $schoolAdmin = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $response = $this->actingAs($schoolAdmin)->post('/admin/sources', [
            'name' => 'School Admin Source',
            'source_type' => 'Internal'
        ]);
        $response->assertStatus(403);

        $response = $this->actingAs($schoolAdmin)->post('/admin/suppliers', [
            'name' => 'School Admin Supplier'
        ]);
        $response->assertStatus(403);
    }

    // --- REQUIREMENT 2: Employee Management (Create/Edit - Scoped to School) ---

    public function test_school_admin_can_register_employee_auto_selects_school()
    {
        $school = School::create(['school_id' => 'SCH-001', 'name' => 'Test School', 'type' => 'Elementary', 'location' => 'ZC']);
        $otherSchool = School::create(['school_id' => 'SCH-002', 'name' => 'Other School', 'type' => 'Elementary', 'location' => 'ZC']);

        $schoolAdmin = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $response = $this->actingAs($schoolAdmin)->post('/admin/employees', [
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'Employee',
            'sex' => 'Male',
            'employee_id' => 'EMP-SCH-999',
            'position' => 'Teacher',
            'date_of_birth' => '1990-01-01',
            'status' => 'Active',
            'school_id' => $otherSchool->id // Trying to spoof other school
        ]);

        $response->assertStatus(302);
        // Assert school_id was auto-selected to the admin's own school, not otherSchool
        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-SCH-999',
            'school_id' => $school->id
        ]);
    }

    public function test_school_admin_cannot_edit_employee_from_other_school()
    {
        $school1 = School::create(['school_id' => 'SCH-001', 'name' => 'School 1', 'type' => 'Elementary', 'location' => 'ZC']);
        $school2 = School::create(['school_id' => 'SCH-002', 'name' => 'School 2', 'type' => 'Elementary', 'location' => 'ZC']);

        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-JDOE',
            'status' => 'Active',
            'school_id' => $school1->id
        ]);

        $school2Admin = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school2->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $response = $this->actingAs($school2Admin)->post("/admin/employees/{$employee->id}/update", [
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Doe',
            'sex' => 'Male',
            'employee_id' => 'EMP-JDOE',
            'position' => 'Teacher',
            'date_of_birth' => '1990-01-01',
            'status' => 'Active',
            'school_id' => $school1->id
        ]);

        // Returns 404 because school_scope global filter shields the query in findOrFail
        $response->assertStatus(404);
    }

    // --- REQUIREMENT 3: Sticker & QR Tag Printing (Only Own Assets) ---

    public function test_school_user_only_prints_own_assets()
    {
        $school1 = School::create(['school_id' => 'SCH-001', 'name' => 'School 1', 'type' => 'Elementary', 'location' => 'ZC']);
        $school2 = School::create(['school_id' => 'SCH-002', 'name' => 'School 2', 'type' => 'Elementary', 'location' => 'ZC']);

        $school1Admin = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school1->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $class = Classification::firstOrCreate(['name' => 'IT Equipment']);
        $cat = Category::firstOrCreate(['name' => 'Laptop'], ['classification_id' => $class->id]);
        $item = Item::firstOrCreate(['name' => 'Acer TravelMate'], ['category_id' => $cat->id]);

        $acq = AcquisitionSource::create(['name' => 'Test Source', 'source_type' => 'Internal']);
        
        $source = AssetSource::create([
            'item_id' => $item->id,
            'acquisition_source_id' => $acq->id,
            'asset_cost' => 10000,
            'quantity' => 2,
            'condition' => 'Good Condition',
            'acceptance_date' => now()->toDateString()
        ]);

        // Asset 1 assigned to School 1
        $assign1 = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source->id,
            'school_id' => $school1->id,
            'acquisition_cost' => 10000,
            'acquisition_date' => now()->toDateString(),
            'property_number' => 'PROP-SCH1-PRINT',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Asset 2 assigned to School 2
        $assign2 = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source->id,
            'school_id' => $school2->id,
            'acquisition_cost' => 10000,
            'acquisition_date' => now()->toDateString(),
            'property_number' => 'PROP-SCH2-PRINT',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($school1Admin)->get('/api/assets/print-list');
        $response->assertStatus(200);
        
        $assets = $response->json()['assets'];
        $propNumbers = collect($assets)->pluck('property_number');

        // Should contain school 1's asset, but not school 2's asset
        $this->assertTrue($propNumbers->contains('PROP-SCH1-PRINT'));
        $this->assertFalse($propNumbers->contains('PROP-SCH2-PRINT'));
    }

    // --- REQUIREMENT 4: System Logs & Explorer (Only Own Updates) ---

    public function test_school_user_only_sees_own_logs()
    {
        $school1 = School::create(['school_id' => 'SCH-001', 'name' => 'School 1', 'type' => 'Elementary', 'location' => 'ZC']);
        $school2 = School::create(['school_id' => 'SCH-002', 'name' => 'School 2', 'type' => 'Elementary', 'location' => 'ZC']);

        $user1 = User::factory()->create([
            'name' => 'School User One',
            'system_type' => 'school',
            'school_id' => $school1->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $user2 = User::factory()->create([
            'name' => 'School User Two',
            'system_type' => 'school',
            'school_id' => $school2->id,
            'role' => 'admin',
            'approved' => true
        ]);

        // Insert log for user 1
        DB::table('system_logs')->insert([
            'user' => 'School User One',
            'action_type' => 'CREATE',
            'module' => 'Employees',
            'activity' => 'Created employee X',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert log for user 2
        DB::table('system_logs')->insert([
            'user' => 'School User Two',
            'action_type' => 'CREATE',
            'module' => 'Employees',
            'activity' => 'Created employee Y',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Call logs endpoint as School User One
        $response = $this->actingAs($user1)->get('/admin/logs');
        $response->assertStatus(200);

        // Assert view data contains the logs filtered to School User One only
        $logs = $response->viewData('logs');
        $usersInLogs = collect($logs->items())->pluck('user');

        $this->assertTrue($usersInLogs->contains('School User One'));
        $this->assertFalse($usersInLogs->contains('School User Two'));
    }
}
