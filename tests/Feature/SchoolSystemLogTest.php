<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolSystemLogTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Seed a minimal acting user so Auth::user() resolves inside the observer.
     */
    private function actingUser(): User
    {
        $user = User::factory()->create([
            'name'     => 'Test Admin',
            'role'     => 'super_admin',
            'approved' => true,
        ]);
        $this->actingAs($user);
        return $user;
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function creating_a_school_via_orm_writes_a_system_log_entry(): void
    {
        $user = $this->actingUser();

        $logCountBefore = DB::table('system_logs')->count();

        // ORM create — triggers the booted() observer
        School::create([
            'school_id'   => 'TEST-001',
            'name'        => 'Test Elementary School',
            'type'        => 'Elementary',
            'location'    => 'Test City',
            'district_id' => null,
        ]);

        $this->assertDatabaseHas('system_logs', [
            'user'        => 'Test Admin',
            'activity'    => 'Added new school: Test Elementary School',
            'module'      => 'Schools',
            'action_type' => 'Create',
        ]);

        $this->assertEquals($logCountBefore + 1, DB::table('system_logs')->count());
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function deleting_a_school_via_orm_writes_a_system_log_entry(): void
    {
        $user = $this->actingUser();

        // ORM create (this also logs Create, but we ignore it here)
        $school = School::create([
            'school_id'   => 'TEST-002',
            'name'        => 'Test High School',
            'type'        => 'Secondary',
            'location'    => 'Test City',
            'district_id' => null,
        ]);

        $logCountAfterCreate = DB::table('system_logs')->count();

        // ORM delete — triggers the booted() deleted observer
        $school->delete();

        $this->assertDatabaseHas('system_logs', [
            'user'        => 'Test Admin',
            'activity'    => 'Deleted school: Test High School',
            'module'      => 'Schools',
            'action_type' => 'Delete',
        ]);

        $this->assertEquals($logCountAfterCreate + 1, DB::table('system_logs')->count());
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function both_create_and_delete_each_produce_exactly_one_log_entry(): void
    {
        $this->actingUser();

        $logCountBefore = DB::table('system_logs')->count();

        $school = School::create([
            'school_id'   => 'TEST-003',
            'name'        => 'Test Special School',
            'type'        => 'Special',
            'location'    => 'Test City',
            'district_id' => null,
        ]);

        // Only 1 log after create
        $this->assertEquals($logCountBefore + 1, DB::table('system_logs')->count());

        $school->delete();

        // Exactly 2 logs total (1 create + 1 delete)
        $this->assertEquals($logCountBefore + 2, DB::table('system_logs')->count());
    }
}
