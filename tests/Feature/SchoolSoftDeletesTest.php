<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\BuildingRecord;
use App\Models\AssetAssignment;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class SchoolSoftDeletesTest extends TestCase
{
    use DatabaseTransactions;

    private function createSchools()
    {
        // Suppress Observer logs during school seeding
        $school1 = School::withoutEvents(function () {
            return School::create([
                'school_id' => 'TSCH-001',
                'name' => 'Test School One',
                'type' => 'Elementary',
                'location' => 'Zamboanga',
            ]);
        });

        $school2 = School::withoutEvents(function () {
            return School::create([
                'school_id' => 'TSCH-002',
                'name' => 'Test School Two',
                'type' => 'Elementary',
                'location' => 'Zamboanga',
            ]);
        });

        return [$school1, $school2];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function school_user_cannot_delete_other_school_employee(): void
    {
        [$school1, $school2] = $this->createSchools();

        $schoolUser = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school1->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $employee = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'school_id' => $school2->id, // different school
            'employee_id' => 'TEMP-EMP999',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($schoolUser)
            ->delete("/admin/employees/{$employee->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'deleted_at' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function school_user_can_soft_delete_own_employee_within_same_day(): void
    {
        [$school1, $school2] = $this->createSchools();

        $schoolUser = User::factory()->create([
            'name' => 'School Admin User',
            'system_type' => 'school',
            'school_id' => $school1->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $employee = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'school_id' => $school1->id,
            'employee_id' => 'TEMP-EMP888',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($schoolUser)
            ->delete("/admin/employees/{$employee->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        
        $this->assertDatabaseHas('system_logs', [
            'user' => 'School Admin User',
            'action_type' => 'Delete',
            'module' => 'Employees'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function school_user_cannot_delete_own_employee_after_limited_window(): void
    {
        [$school1, $school2] = $this->createSchools();

        $schoolUser = User::factory()->create([
            'system_type' => 'school',
            'school_id' => $school1->id,
            'role' => 'admin',
            'approved' => true
        ]);

        $employee = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'school_id' => $school1->id,
            'employee_id' => 'TEMP-EMP777',
            'status' => 'Active',
        ]);

        DB::table('employees')->where('id', $employee->id)->update([
            'created_at' => now()->subDays(2)
        ]);

        $response = $this->actingAs($schoolUser)
            ->delete("/admin/employees/{$employee->id}");

        $response->assertSessionHas('error', 'Same-day deletion window has expired for this employee.');
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'deleted_at' => null]);
    }
}
