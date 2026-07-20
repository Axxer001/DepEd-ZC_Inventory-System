<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\AssetAssignment;
use App\Models\AssetSource;
use App\Models\Item;
use App\Models\Category;
use App\Models\Classification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetHardDeleteTest extends TestCase
{
    use DatabaseTransactions;

    private function setupAsset($qty = 1)
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'system_type' => 'main',
            'approved' => true,
        ]);

        $classification = Classification::firstOrCreate(['name' => 'Test Classification']);
        $category = Category::firstOrCreate([
            'name' => 'Test Category',
        ], [
            'classification_id' => $classification->id,
            'see_category_code' => 'SEE-01',
            'ppe_category_code' => 'PPE-01',
        ]);
        $item = Item::firstOrCreate([
            'name' => 'Test Item',
        ], [
            'category_id' => $category->id,
        ]);

        $acqSource = \App\Models\AcquisitionSource::firstOrCreate([
            'name' => 'Test Source',
        ], [
            'type' => 'Donation',
        ]);

        $source = AssetSource::create([
            'item_id' => $item->id,
            'acquisition_source_id' => $acqSource->id,
            'description' => 'Test Sourced Item',
            'condition' => 'Good Condition',
            'asset_cost' => 1000.00,
            'quantity' => $qty,
            'acceptance_date' => now()->toDateString(),
        ]);

        $assignment = AssetAssignment::create([
            'asset_source_id' => $source->id,
            'property_number' => 'PROP-' . rand(1000, 9999),
            'serial_number' => 'SER-' . rand(1000, 9999),
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 1000.00,
            'school_id' => 1,
        ]);

        return [$admin, $assignment, $source];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_hard_delete_asset(): void
    {
        Storage::fake('public');

        [$admin, $assignment, $source] = $this->setupAsset(2);

        // Upload fake photo and document
        $assignment->photo_path = 'assets/fake_photo.jpg';
        $assignment->save();

        Storage::disk('public')->put('assets/fake_photo.jpg', 'content');
        Storage::disk('public')->put('documents/fake_doc.pdf', 'content');

        DB::table('asset_documents')->insert([
            'asset_distribution_id' => $assignment->id,
            'file_name' => 'fake_doc.pdf',
            'file_path' => 'documents/fake_doc.pdf',
            'file_size' => 1234,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('asset_transfers')->insert([
            'asset_assignment_id' => $assignment->id,
            'transfer_type' => 'Archive',
            'from_school_id' => 1,
            'to_school_id' => null,
            'transfer_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify files exist in storage
        Storage::disk('public')->assertExists('assets/fake_photo.jpg');
        Storage::disk('public')->assertExists('documents/fake_doc.pdf');

        // Delete asset
        $response = $this->actingAs($admin)
            ->delete("/assets/{$assignment->id}/delete", [
                'confirm_delete' => 'DELETE'
            ]);

        $response->assertRedirect(route('assets.view'));
        $response->assertSessionHasNoErrors();

        // Assert database records are gone
        $this->assertDatabaseMissing('asset_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('asset_documents', ['asset_distribution_id' => $assignment->id]);
        $this->assertDatabaseMissing('asset_transfers', ['asset_assignment_id' => $assignment->id]);

        // Assert physical files deleted
        Storage::disk('public')->assertMissing('assets/fake_photo.jpg');
        Storage::disk('public')->assertMissing('documents/fake_doc.pdf');

        // Assert quantity decremented from 2 to 1
        $this->assertDatabaseHas('asset_sources', [
            'id' => $source->id,
            'quantity' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_bulk_hard_delete_assets(): void
    {
        Storage::fake('public');

        [$admin, $asset1, $source1] = $this->setupAsset(1);
        [$admin2, $asset2, $source2] = $this->setupAsset(1);

        $response = $this->actingAs($admin)
            ->postJson("/api/assets/bulk-delete", [
                'ids' => [$asset1->id, $asset2->id],
                'confirm_delete' => 'DELETE'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'Selected assets successfully deleted.']);

        $this->assertDatabaseMissing('asset_assignments', ['id' => $asset1->id]);
        $this->assertDatabaseMissing('asset_assignments', ['id' => $asset2->id]);
        $this->assertDatabaseMissing('asset_sources', ['id' => $source1->id]);
        $this->assertDatabaseMissing('asset_sources', ['id' => $source2->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function school_user_cannot_delete_assets(): void
    {
        [$admin, $assignment, $source] = $this->setupAsset(1);

        $schoolUser = User::factory()->create([
            'role' => 'admin',
            'system_type' => 'school',
            'school_id' => 1,
            'approved' => true,
        ]);

        $response = $this->actingAs($schoolUser)
            ->delete("/assets/{$assignment->id}/delete");

        $response->assertStatus(403);

        $responseBulk = $this->actingAs($schoolUser)
            ->postJson("/api/assets/bulk-delete", [
                'ids' => [$assignment->id]
            ]);

        $responseBulk->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function superadmin_deletion_fails_without_correct_confirm_delete(): void
    {
        [$admin, $assignment, $source] = $this->setupAsset(1);

        $response = $this->actingAs($admin)
            ->delete("/assets/{$assignment->id}/delete", [
                'confirm_delete' => 'INVALID'
            ]);

        $response->assertSessionHas('error', 'Confirmation failed. You must type DELETE to confirm deletion.');
        $this->assertDatabaseHas('asset_assignments', ['id' => $assignment->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_mains_cannot_delete_assigned_asset(): void
    {
        [$admin, $assignment, $source] = $this->setupAsset(1);

        $mainsAdmin = User::factory()->create([
            'role' => 'admin',
            'system_type' => 'main',
            'approved' => true,
        ]);

        // Create an active assignment to block deletion
        $employee = \App\Models\Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_id' => 'EMP-TEST-99',
            'approved' => true,
            'status' => 'Active',
        ]);
        $assignment->update(['employee_id' => $employee->id]);

        $response = $this->actingAs($mainsAdmin)
            ->delete("/assets/{$assignment->id}/delete");

        $response->assertSessionHas('error', 'Deletion rejected. This asset has been assigned to a custodian and cannot be deleted by an Admin.');
        $this->assertDatabaseHas('asset_assignments', ['id' => $assignment->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_mains_can_delete_unassigned_asset(): void
    {
        [$admin, $assignment, $source] = $this->setupAsset(2);

        $mainsAdmin = User::factory()->create([
            'role' => 'admin',
            'system_type' => 'main',
            'approved' => true,
        ]);

        $response = $this->actingAs($mainsAdmin)
            ->delete("/assets/{$assignment->id}/delete");

        $response->assertRedirect(route('assets.view'));
        $this->assertDatabaseMissing('asset_assignments', ['id' => $assignment->id]);
    }
}
