<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Centralised "Safe Permanent Delete" eligibility checker.
 *
 * Returns an array of human-readable blocking reasons for each entity.
 * An empty array means the record is safe to hard-delete.
 */
class DeletionEligibilityService
{
    // -------------------------------------------------------------------------
    // EMPLOYEE
    // -------------------------------------------------------------------------

    /**
     * @return string[]  Blocking reasons; empty = eligible for hard-delete.
     */
    public function checkEmployee(int $id): array
    {
        $reasons = [];

        $assignments = DB::table('asset_assignments')
            ->where('employee_id', $id)
            ->count();
        if ($assignments > 0) {
            $reasons[] = "Has {$assignments} asset assignment(s) on record.";
        }

        $transfers = DB::table('asset_transfers')
            ->where(function ($q) use ($id) {
                $q->where('from_custodian_id', $id)
                  ->orWhere('to_custodian_id', $id);
            })
            ->count();
        if ($transfers > 0) {
            $reasons[] = "Appears in {$transfers} asset transfer record(s).";
        }

        return $reasons;
    }

    // -------------------------------------------------------------------------
    // CLASSIFICATION
    // -------------------------------------------------------------------------

    /**
     * @return string[]
     */
    public function checkClassification(int $id): array
    {
        $reasons = [];

        $categoryCount = DB::table('categories')
            ->where('classification_id', $id)
            ->count();
        if ($categoryCount > 0) {
            $reasons[] = "Contains {$categoryCount} category(ies). Delete or reassign them first.";
        }

        return $reasons;
    }

    // -------------------------------------------------------------------------
    // CATEGORY
    // -------------------------------------------------------------------------

    /**
     * @return string[]
     */
    public function checkCategory(int $id): array
    {
        $reasons = [];

        $itemCount = DB::table('items')
            ->where('category_id', $id)
            ->count();
        if ($itemCount > 0) {
            $reasons[] = "Has {$itemCount} item(s) (asset type definitions) linked to it.";
        }

        return $reasons;
    }

    // -------------------------------------------------------------------------
    // SUPPLIER
    // -------------------------------------------------------------------------

    /**
     * @return string[]
     */
    public function checkSupplier(int $id): array
    {
        $reasons = [];

        $assetSources = DB::table('asset_sources')
            ->where('supplier_id', $id)
            ->count();
        if ($assetSources > 0) {
            $reasons[] = "Linked to {$assetSources} asset source record(s) (procurement batches).";
        }

        $services = DB::table('asset_services')
            ->where('supplier_id', $id)
            ->count();
        if ($services > 0) {
            $reasons[] = "Referenced in {$services} asset service/repair record(s).";
        }

        return $reasons;
    }

    // -------------------------------------------------------------------------
    // ACQUISITION SOURCE
    // -------------------------------------------------------------------------

    /**
     * @return string[]
     */
    public function checkAcquisitionSource(int $id): array
    {
        $reasons = [];

        $assetSources = DB::table('asset_sources')
            ->where('acquisition_source_id', $id)
            ->count();
        if ($assetSources > 0) {
            $reasons[] = "Linked to {$assetSources} asset source record(s) (procurement batches).";
        }

        return $reasons;
    }
}
