<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Illuminate\Support\Facades\DB::table('asset_transfers as at')
            ->join('asset_assignments as ad', 'at.asset_assignment_id', '=', 'ad.id')
            ->where('at.transfer_type', 'Initial Distribution')
            ->update([
                'at.to_school_id' => Illuminate\Support\Facades\DB::raw('COALESCE(at.to_school_id, ad.school_id)'),
                'at.to_office_id' => Illuminate\Support\Facades\DB::raw('COALESCE(at.to_office_id, ad.office_id)'),
                'at.to_custodian_id' => Illuminate\Support\Facades\DB::raw('COALESCE(at.to_custodian_id, ad.employee_id)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or revert changes back to null if needed, but keeping them aligned is preferred.
    }
};
