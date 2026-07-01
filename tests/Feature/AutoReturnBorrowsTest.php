<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AssetTransferNotification;

class AutoReturnBorrowsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_temporary_borrow_is_automatically_returned_when_due_date_passed()
    {
        Notification::fake();

        // 1. Create a mock admin user who will receive notification
        $admin = User::factory()->create(['approved' => true, 'role' => 'admin']);

        // 2. Create employees for lender and borrower
        $lenderId = DB::table('employees')->insertGetId([
            'first_name' => 'Lender',
            'last_name' => 'Custodian',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $borrowerId = DB::table('employees')->insertGetId([
            'first_name' => 'Borrower',
            'last_name' => 'Custodian',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create classifications, categories, items, asset_sources
        $classId = DB::table('classifications')->insertGetId([
            'name' => 'TEST CLASS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catId = DB::table('categories')->insertGetId([
            'name' => 'TEST CAT',
            'classification_id' => $classId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemId = DB::table('items')->insertGetId([
            'name' => 'TEST ITEM',
            'category_id' => $catId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $acqId = DB::table('acquisition_sources')->insertGetId([
            'name' => 'TEST SOURCE',
            'source_type' => 'Internal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sourceId = DB::table('asset_sources')->insertGetId([
            'item_id' => $itemId,
            'description' => 'Test Laptop',
            'quantity' => 1,
            'asset_cost' => 50000.00,
            'acquisition_source_id' => $acqId,
            'acceptance_date' => now()->subDays(10)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Assign asset to the borrower (representing current state of temporary borrow)
        $assetId = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $sourceId,
            'employee_id' => $borrowerId,
            'property_number' => 'PROP-AUTO-TEST-123',
            'acquisition_cost' => 50000.00,
            'acquisition_date' => now()->subDays(5)->toDateString(),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // 5. Insert the log of Temporary Borrow in asset_transfers (due yesterday)
        DB::table('asset_transfers')->insert([
            'asset_assignment_id' => $assetId,
            'from_custodian_id' => $lenderId,
            'to_custodian_id' => $borrowerId,
            'transfer_date' => now()->subDays(5)->toDateString(),
            'return_date' => now()->subDay()->toDateString(), // Due yesterday
            'transfer_type' => 'Temporary Borrow',
            'authorized_by' => $admin->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // 6. Run the artisan command
        $this->artisan('app:auto-return-borrows')
            ->assertExitCode(0);

        // 7. Verify asset is assigned back to lender (original owner)
        $this->assertDatabaseHas('asset_assignments', [
            'id' => $assetId,
            'employee_id' => $lenderId,
        ]);

        // 8. Verify the return transfer is logged
        $this->assertDatabaseHas('asset_transfers', [
            'asset_assignment_id' => $assetId,
            'from_custodian_id' => $borrowerId,
            'to_custodian_id' => $lenderId,
            'transfer_type' => 'Return',
        ]);

        // 9. Verify notification was sent
        Notification::assertSentTo(
            [$admin],
            AssetTransferNotification::class,
            function ($notification) {
                return strpos($notification->data->detailed_message, 'Automatically returned') !== false;
            }
        );
    }

    public function test_temporary_borrow_is_not_returned_early()
    {
        Notification::fake();

        $admin = User::factory()->create(['approved' => true, 'role' => 'admin']);

        $lenderId = DB::table('employees')->insertGetId([
            'first_name' => 'Lender',
            'last_name' => 'Custodian',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $borrowerId = DB::table('employees')->insertGetId([
            'first_name' => 'Borrower',
            'last_name' => 'Custodian',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create classifications, categories, items, asset_sources
        $classId = DB::table('classifications')->insertGetId([
            'name' => 'TEST CLASS 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catId = DB::table('categories')->insertGetId([
            'name' => 'TEST CAT 2', 
            'classification_id' => $classId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $itemId = DB::table('items')->insertGetId([
            'name' => 'TEST ITEM 2', 
            'category_id' => $catId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $acqId = DB::table('acquisition_sources')->insertGetId([
            'name' => 'TEST SOURCE 2', 
            'source_type' => 'Internal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sourceId = DB::table('asset_sources')->insertGetId([
            'item_id' => $itemId,
            'description' => 'Test Laptop 2',
            'quantity' => 1,
            'asset_cost' => 50000.00,
            'acquisition_source_id' => $acqId,
            'acceptance_date' => now()->subDays(10)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assetId = DB::table('asset_assignments')->insertGetId([
            'asset_source_id' => $sourceId,
            'employee_id' => $borrowerId,
            'property_number' => 'PROP-AUTO-TEST-456',
            'acquisition_cost' => 50000.00,
            'acquisition_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('asset_transfers')->insert([
            'asset_assignment_id' => $assetId,
            'from_custodian_id' => $lenderId,
            'to_custodian_id' => $borrowerId,
            'transfer_date' => now()->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(), // Due in 5 days
            'transfer_type' => 'Temporary Borrow',
            'authorized_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the artisan command
        $this->artisan('app:auto-return-borrows')
            ->assertExitCode(0);

        // Verify asset is still assigned to the borrower
        $this->assertDatabaseHas('asset_assignments', [
            'id' => $assetId,
            'employee_id' => $borrowerId,
        ]);

        // Verify no return transfer has been logged
        $this->assertDatabaseMissing('asset_transfers', [
            'asset_assignment_id' => $assetId,
            'transfer_type' => 'Return',
        ]);

        Notification::assertNothingSent();
    }
}
