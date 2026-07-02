<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class EmployeeRoutingTest extends TestCase
{
    use DatabaseTransactions;

    private function getSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'approved' => true,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function super_admin_can_register_employee_and_redirects_to_standard_route(): void
    {
        $this->withoutExceptionHandling();
        $superAdmin = $this->getSuperAdmin();

        $response = $this->actingAs($superAdmin)->post(route('admin.employees.store'), [
            'first_name' => 'Jane',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-TEST-999',
            'sex' => 'Female',
            'date_of_birth' => '1990-01-01',
            'position' => 'Teacher I',
            'status' => 'Active',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'employee_id' => 'EMP-TEST-999',
            'first_name' => 'Jane',
        ]);

        $response->assertRedirect(route('admin.employees'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function super_admin_can_update_employee_and_redirects_to_profile(): void
    {
        $superAdmin = $this->getSuperAdmin();

        $employee = Employee::create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'employee_id' => 'EMP-TEST-888',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.employees.update', $employee->id), [
            'first_name' => 'Robert',
            'last_name' => 'Smith',
            'employee_id' => 'EMP-TEST-888',
            'status' => 'Active',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Robert',
        ]);

        $response->assertRedirect(route('custodians.profile', $employee->id));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function legacy_employee_management_routes_resolve_successfully(): void
    {
        $superAdmin = $this->getSuperAdmin();

        $employee = Employee::create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'employee_id' => 'EMP-TEST-777',
            'status' => 'Active',
        ]);

        // Test index route alias
        $responseIndex = $this->actingAs($superAdmin)->get(route('admin.employee-management'));
        $responseIndex->assertStatus(200);

        // Test profile route alias
        $responseProfile = $this->actingAs($superAdmin)->get(route('admin.employee-management.profile', $employee->id));
        $responseProfile->assertStatus(200);
    }
}
