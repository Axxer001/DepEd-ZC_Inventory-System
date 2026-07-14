<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\School;
use App\Models\AssetAssignment;
use App\Models\AssetSource;
use App\Models\Category;
use App\Models\Classification;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class DocumentationDownloadTest extends TestCase
{
    use DatabaseTransactions;

    private function getSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'approved' => true,
        ]);
    }

    private function createAsset($cost = 10000): int
    {
        $class = Classification::firstOrCreate(['name' => 'IT Equipment']);
        $cat = Category::firstOrCreate(['name' => 'Laptop'], ['classification_id' => $class->id]);
        $item = Item::firstOrCreate(['name' => 'Acer TravelMate'], ['category_id' => $cat->id]);
        
        $acq = \App\Models\AcquisitionSource::firstOrCreate(['name' => 'DepEd Central Office'], ['source_type' => 'Internal']);
        $source = AssetSource::create([
            'item_id' => $item->id,
            'acquisition_source_id' => $acq->id,
            'asset_cost' => $cost,
            'quantity' => 1,
            'condition' => 'Good Condition',
            'acceptance_date' => now()->toDateString()
        ]);

        return DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $source->id,
            'acquisition_cost' => $cost,
            'acquisition_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function template_download_endpoint_works_and_renames_correctly(): void
    {
        $superAdmin = $this->getSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.download_doc_template', [
            'type' => 'PAR',
            'recipient' => 'John Doe'
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="PARJohn Doe.xlsx"');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function template_download_endpoint_populates_fields_with_assignment_id(): void
    {
        $superAdmin = $this->getSuperAdmin();
        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-POP-TEST',
            'status' => 'Active',
        ]);
        $assignmentId = $this->createAsset(25000);
        DB::table('asset_assignments')->where('id', $assignmentId)->update([
            'employee_id' => $employee->id,
            'property_number' => 'PROP-POP-123',
            'acquisition_date' => '2026-07-08'
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.download_doc_template', [
            'type' => 'ICS',
            'recipient' => 'John Doe',
            'assignment_id' => $assignmentId
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function assign_item_flashes_documentation_to_session(): void
    {
        $superAdmin = $this->getSuperAdmin();
        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-TEST-DOC',
            'status' => 'Active',
        ]);

        $assignmentId = $this->createAsset(25000); // <= 49999 is ICS

        $response = $this->actingAs($superAdmin)->post(route('assign_asset.store'), [
            'assignment_id' => $assignmentId,
            'employee_id' => $employee->id,
            'property_number' => 'PROP-123',
            'acquisition_date' => '2026-07-07'
        ]);

        $response->assertStatus(200);
        $response->assertSessionHas('download_docs');

        $downloadDocs = session('download_docs');
        $this->assertCount(1, $downloadDocs);
        $this->assertEquals('John Doe', $downloadDocs[0]['recipient_name']);
        $this->assertEquals('ICS', $downloadDocs[0]['doc_type']);
        $this->assertEquals($assignmentId, $downloadDocs[0]['assignment_id']);
        $this->assertNotEmpty($downloadDocs[0]['transfer_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function initiate_transfer_flashes_documentation_on_employee_to_employee(): void
    {
        $superAdmin = $this->getSuperAdmin();
        
        $employee1 = Employee::create([
            'first_name' => 'Source',
            'last_name' => 'User',
            'employee_id' => 'EMP-SRC',
            'status' => 'Active',
        ]);
        
        $employee2 = Employee::create([
            'first_name' => 'Target',
            'last_name' => 'User',
            'employee_id' => 'EMP-TGT',
            'status' => 'Active',
        ]);

        $assignmentId = $this->createAsset(60000); // > 49999 is PTR/PAR

        // Set initial custodian
        DB::table('asset_assignments')->where('id', $assignmentId)->update([
            'employee_id' => $employee1->id
        ]);

        $response = $this->actingAs($superAdmin)->post(route('assets.transfer', $assignmentId), [
            'employee_id' => $employee2->id,
            'transfer_type' => 'Permanent Reassignment',
            'condition' => 'Good Condition',
            'remarks' => 'Transferring to another teacher'
        ]);

        $response->assertSessionHas('download_docs');
        $downloadDocs = session('download_docs');
        $this->assertCount(1, $downloadDocs);
        $this->assertEquals('Target User', $downloadDocs[0]['recipient_name']);
        $this->assertEquals('PTR', $downloadDocs[0]['doc_type']);
        $this->assertEquals($assignmentId, $downloadDocs[0]['assignment_id']);
        $this->assertNotEmpty($downloadDocs[0]['transfer_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function return_to_amu_flashes_documentation_based_on_cost(): void
    {
        $superAdmin = $this->getSuperAdmin();
        
        $employee = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-RET',
            'status' => 'Active',
        ]);

        $assignmentId = $this->createAsset(15000); // <= 49999 is RRSP

        // Set initial custodian
        DB::table('asset_assignments')->where('id', $assignmentId)->update([
            'employee_id' => $employee->id
        ]);

        $response = $this->actingAs($superAdmin)->post(route('assets.return', $assignmentId), [
            'return_date' => '2026-07-07',
            'condition' => 'Good Condition',
            'remarks' => 'Returning asset to warehouse'
        ]);

        $response->assertSessionHas('download_docs');
        $downloadDocs = session('download_docs');
        $this->assertCount(1, $downloadDocs);
        $this->assertEquals('Jane Doe', $downloadDocs[0]['recipient_name']);
        $this->assertEquals('RRSP', $downloadDocs[0]['doc_type']);
        $this->assertEquals($assignmentId, $downloadDocs[0]['assignment_id']);
        $this->assertNotEmpty($downloadDocs[0]['transfer_id']);
    }
}
