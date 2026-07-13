<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Issue 1 — Atomic Per-Scope Sequential Property Number Generator
 *
 * Replaces any naive MAX()+1 pattern with a proper row-locking approach.
 *
 * Rules (per the remediation plan):
 *  - Each school has its own independent counter.
 *  - Main (SDO) has its own separate counter.
 *  - The scope is determined by who the asset is ASSIGNED TO at creation time
 *    (the target school if directly assigned, Main if unassigned).
 *  - Once a number is issued, it is NEVER recalculated or reassigned on transfer.
 *    Call this method only at initial creation, not in transfer flows.
 *
 * Concurrency safety:
 *  - Uses SELECT ... FOR UPDATE (lockForUpdate()) inside a DB::transaction()
 *    so two simultaneous requests for the same school wait on each other
 *    rather than both reading the same current_number.
 *  - Requests for DIFFERENT scopes (different schools) don't block each other
 *    at all — each school has its own row.
 */
class PropertyNumberService
{
    /**
     * Generate and reserve the next property number for the given scope.
     *
     * @param  int|null  $schoolId  NULL → Main scope; school PK → school scope
     * @return string  e.g. "MAIN-0001" or "SCH-0042"
     */
    public function next(?int $schoolId = null): string
    {
        return DB::transaction(function () use ($schoolId) {
            $scopeType = $schoolId ? 'school' : 'main';

            $sequence = DB::table('asset_sequences')
                ->where('scope_type', $scopeType)
                ->where('school_id', $schoolId)   // handles NULL safely via SQL IS NULL / = N
                ->lockForUpdate()                  // SELECT ... FOR UPDATE: blocks concurrent readers until commit
                ->first();

            if (!$sequence) {
                // Upsert — handles the very first call for a newly created school
                DB::table('asset_sequences')->insert([
                    'scope_type'     => $scopeType,
                    'school_id'      => $schoolId,
                    'current_number' => 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Re-acquire with lock now that the row exists
                $sequence = DB::table('asset_sequences')
                    ->where('scope_type', $scopeType)
                    ->where('school_id', $schoolId)
                    ->lockForUpdate()
                    ->first();
            }

            $nextNumber = $sequence->current_number + 1;

            DB::table('asset_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'current_number' => $nextNumber,
                    'updated_at'     => now(),
                ]);

            $prefix = $schoolId ? 'SCH' : 'MAIN';

            return $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Resolve the correct scope for a batch of items.
     *
     * Per the plan's edge-case requirement: in a batch registration where some
     * items go to School A, some to School B, and some are unassigned, this
     * method must be called PER LINE ITEM — not once for the whole batch.
     * This ensures each item pulls from the correct scope's counter and that
     * locks on different schools don't block each other unnecessarily.
     *
     * Usage:
     *   foreach ($batchItems as $item) {
     *       $item['property_number'] = $this->propertyNumberService->next($item['school_id'] ?? null);
     *   }
     *
     * @param  int|null  $schoolId
     * @return string
     */
    public function nextForItem(?int $schoolId = null): string
    {
        return $this->next($schoolId);
    }
}
