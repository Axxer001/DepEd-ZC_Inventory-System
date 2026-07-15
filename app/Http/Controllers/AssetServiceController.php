<?php

namespace App\Http\Controllers;

use App\Models\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetServiceController extends Controller
{
    /**
     * List all assets currently under repair.
     */
    public function index()
    {
        $services = DB::table('asset_services as asv')
            ->join('asset_assignments as ad', 'asv.asset_assignment_id', '=', 'ad.id')
            ->join('asset_sources as asrc', 'asv.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('suppliers', 'asv.supplier_id', '=', 'suppliers.id')
            ->leftJoin('employees as prev_emp', 'asv.previous_custodian_id', '=', 'prev_emp.id')
            ->select(
                'asv.id',
                'asv.expected_return_date',
                'asv.created_at as sent_date',
                'ad.id as assignment_id',
                'ad.property_number',
                'asrc.condition',
                'asrc.description',
                'items.name as item_name',
                'suppliers.name as supplier_name',
                DB::raw('COALESCE(asrc.supplier_service_center, suppliers.service_center) as service_center'),
                DB::raw("CONCAT(COALESCE(prev_emp.first_name,''), ' ', COALESCE(prev_emp.last_name,'')) as previous_custodian_name"),
                'asv.previous_custodian_id'
            )
            ->orderBy('asv.expected_return_date', 'asc')
            ->get()
            ->map(function ($row) {
                $expected = Carbon::parse($row->expected_return_date);
                $now      = Carbon::now();
                $row->is_overdue      = $now->greaterThan($expected);
                $row->overdue_days    = $row->is_overdue ? $this->getPhWorkingDaysDiff($expected, $now) : 0;
                return $row;
            });

        return view('assets.service.index', compact('services'));
    }

    /**
     * Show the Service Profile for a single repair record.
     */
    public function show($id)
    {
        $service = DB::table('asset_services as asv')
            ->join('asset_assignments as ad', 'asv.asset_assignment_id', '=', 'ad.id')
            ->join('asset_sources as asrc', 'asv.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('classifications', 'categories.classification_id', '=', 'classifications.id')
            ->join('acquisition_sources', 'asrc.acquisition_source_id', '=', 'acquisition_sources.id')
            ->join('suppliers', 'asv.supplier_id', '=', 'suppliers.id')
            ->leftJoin('procurement_modes as pm', 'asrc.procurement_mode_id', '=', 'pm.id')
            ->leftJoin('employees as prev_emp', 'asv.previous_custodian_id', '=', 'prev_emp.id')
            ->leftJoin('offices as prev_off', 'prev_emp.office_id', '=', 'prev_off.id')
            ->leftJoin('schools as prev_sch', 'prev_emp.school_id', '=', 'prev_sch.id')
            ->where('asv.id', $id)
            ->select(
                'asv.id',
                'asv.expected_return_date',
                'asv.created_at as sent_date',
                'asv.previous_custodian_id',
                'ad.id as assignment_id',
                'ad.property_number',
                'ad.acquisition_cost',
                'ad.acquisition_date',
                'asrc.id as asset_source_id',
                'ad.photo_path',
                'asrc.condition',
                'asrc.description',
                'asrc.asset_cost',
                'asrc.quantity',
                'asrc.unit_of_measurement',
                'asrc.estimated_useful_life',
                'asrc.warranty',
                'asrc.acceptance_date',
                'items.name as item_name',
                'items.id as item_id',
                'categories.name as category_name',
                'classifications.name as classification_name',
                'acquisition_sources.name as source_name',
                'suppliers.name as supplier_name',
                DB::raw('COALESCE(asrc.supplier_service_center, suppliers.service_center) as service_center'),
                DB::raw('COALESCE(asrc.supplier_contact_number, suppliers.contact_number) as supplier_contact'),
                'pm.name as mode_of_acquisition',
                'prev_emp.first_name as prev_first',
                'prev_emp.middle_name as prev_middle',
                'prev_emp.last_name as prev_last',
                'prev_emp.position as prev_position',
                'prev_emp.employee_id as prev_employee_code',
                DB::raw('COALESCE(prev_sch.name, prev_off.name) as prev_location_name')
            )
            ->first();

        if (!$service) {
            abort(404, 'Service record not found.');
        }

        // Compute progress percentage
        $sentDate     = Carbon::parse($service->sent_date);
        $expectedDate = Carbon::parse($service->expected_return_date);
        $now          = Carbon::now();

        $totalDays   = max(1, $sentDate->diffInDays($expectedDate));
        $elapsed     = $sentDate->diffInDays($now, false);
        $progress    = max(0, min(100, round((($totalDays - $elapsed) / $totalDays) * 100)));
        $isOverdue   = $now->greaterThan($expectedDate);
        $overdueDays = $isOverdue ? $this->getPhWorkingDaysDiff($expectedDate, $now) : 0;

        $prevCustodianFullName = trim(
            ($service->prev_first ?? '') . ' ' .
            ($service->prev_middle ? $service->prev_middle . ' ' : '') .
            ($service->prev_last ?? '')
        );

        return view('assets.service.show', compact(
            'service',
            'progress',
            'isOverdue',
            'overdueDays',
            'prevCustodianFullName'
        ));
    }

    /**
     * Return the repaired asset to its previous custodian.
     */
    public function returnToCustodian(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $serviceRecord = DB::table('asset_services')->where('id', $id)->first();
        if (!$serviceRecord) {
            return back()->with('error', 'Service record not found.');
        }

        if (!$serviceRecord->previous_custodian_id) {
            return back()->with('error', 'No previous custodian saved. Use "Return to AMU" instead.');
        }

        $assignmentId = $serviceRecord->asset_assignment_id;
        $expected     = Carbon::parse($serviceRecord->expected_return_date);
        $actual       = Carbon::now();
        $daysDiff     = $this->getPhWorkingDaysDiff($expected, $actual);

        // Retrieve custodian's office for the transfer log
        $custodian    = DB::table('employees')->where('id', $serviceRecord->previous_custodian_id)->first();
        $toOfficeId   = $custodian?->office_id ?? null;

        $supName = DB::table('suppliers')->where('id', $serviceRecord->supplier_id)->value('name') ?? 'Supplier';

        DB::transaction(function () use ($serviceRecord, $assignmentId, $daysDiff, $expected, $actual, $toOfficeId, $supName) {
            // Reassign custodian
            DB::table('asset_assignments')->where('id', $assignmentId)->update([
                'employee_id' => $serviceRecord->previous_custodian_id,
                'updated_at'  => now(),
            ]);

            // Set condition to Good Condition
            DB::table('asset_sources')->where('id', $serviceRecord->asset_source_id)->update([
                'condition'  => 'Good Condition',
                'updated_at' => now(),
            ]);

            // Log transfer history
            DB::table('asset_transfers')->insert([
                'asset_assignment_id'  => $assignmentId,
                'from_office_id'       => null,
                'to_office_id'         => $toOfficeId,
                'from_custodian_id'    => null,
                'to_custodian_id'      => $serviceRecord->previous_custodian_id,
                'transfer_date'        => $actual->toDateString(),
                'transfer_type'        => 'Return to Custodian',
                'remarks'              => "Returned from supplier repair ({$supName}) to original custodian.",
                'authorized_by'        => Auth::id() ?? 1,
                'expected_return_date' => $expected->toDateString(),
                'days_difference'      => $daysDiff,
                'repair_status'        => 'Completed - Returned to Custodian',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Remove the service record
            DB::table('asset_services')->where('id', $serviceRecord->id)->delete();

            // Notify all admins
            $assignment = DB::table('asset_assignments')->where('id', $assignmentId)->first();
            $source     = DB::table('asset_sources')->where('id', $serviceRecord->asset_source_id)->first();
            $itemName   = DB::table('items')->where('id', $source->item_id)->value('name') ?? 'Item';
            $propNo     = $assignment->property_number ? "[{$assignment->property_number}] " : '';
            $diffLabel  = $daysDiff === 0 ? 'on time' : ($daysDiff < 0 ? abs($daysDiff) . ' working days early' : "{$daysDiff} working days late");
            $custodian  = DB::table('employees')->where('id', $serviceRecord->previous_custodian_id)->first();
            $custName   = $custodian ? trim($custodian->first_name . ' ' . $custodian->last_name) : 'Custodian';

            $dummyAsset = (object)[
                'title'            => 'Asset Repair Completed',
                'message'          => "A repaired asset has been returned to its custodian.",
                'detailed_message' => "Repair completed for {$propNo}{$itemName}. Returned to {$custName} ({$diffLabel}). Supplier: {$supName}.",
            ];

            $schoolId = $assignment ? $assignment->school_id : null;
            if (!$schoolId && $custodian) {
                $schoolId = $custodian->school_id;
            }
            $admins = \App\Models\User::getNotificationRecipients($schoolId);
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetReturnedNotification($dummyAsset));
            }
        });

        return redirect()->route('asset.service.index')->with('success', 'Asset has been successfully returned to its custodian and marked as Good Condition!');
    }

    /**
     * Return the repaired asset to AMU / Warehouse.
     */
    public function returnToAmu(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->approved) {
            abort(403, 'Unauthorized action.');
        }

        $serviceRecord = DB::table('asset_services')->where('id', $id)->first();
        if (!$serviceRecord) {
            return back()->with('error', 'Service record not found.');
        }

        $assignmentId = $serviceRecord->asset_assignment_id;
        $expected     = Carbon::parse($serviceRecord->expected_return_date);
        $actual       = Carbon::now();
        $daysDiff     = $this->getPhWorkingDaysDiff($expected, $actual);

        $supName = DB::table('suppliers')->where('id', $serviceRecord->supplier_id)->value('name') ?? 'Supplier';

        DB::transaction(function () use ($serviceRecord, $assignmentId, $daysDiff, $expected, $actual, $supName) {
            // Ensure unassigned
            DB::table('asset_assignments')->where('id', $assignmentId)->update([
                'employee_id' => null,
                'updated_at'  => now(),
            ]);

            // Set condition to Good Condition
            DB::table('asset_sources')->where('id', $serviceRecord->asset_source_id)->update([
                'condition'  => 'Good Condition',
                'updated_at' => now(),
            ]);

            // Log transfer history
            DB::table('asset_transfers')->insert([
                'asset_assignment_id'  => $assignmentId,
                'from_office_id'       => null,
                'to_office_id'         => null,
                'from_custodian_id'    => null,
                'to_custodian_id'      => null,
                'transfer_date'        => $actual->toDateString(),
                'transfer_type'        => 'Return',
                'remarks'              => "Returned from supplier repair ({$supName}) directly to AMU / Warehouse.",
                'authorized_by'        => Auth::id() ?? 1,
                'expected_return_date' => $expected->toDateString(),
                'days_difference'      => $daysDiff,
                'repair_status'        => 'Completed - Returned to AMU',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Remove the service record
            DB::table('asset_services')->where('id', $serviceRecord->id)->delete();

            // Notify all admins
            $assignment = DB::table('asset_assignments')->where('id', $assignmentId)->first();
            $source     = DB::table('asset_sources')->where('id', $serviceRecord->asset_source_id)->first();
            $itemName   = DB::table('items')->where('id', $source->item_id)->value('name') ?? 'Item';
            $propNo     = $assignment->property_number ? "[{$assignment->property_number}] " : '';
            $diffLabel  = $daysDiff === 0 ? 'on time' : ($daysDiff < 0 ? abs($daysDiff) . ' working days early' : "{$daysDiff} working days late");

            $dummyAsset = (object)[
                'title'            => 'Asset Repair Completed',
                'message'          => "A repaired asset has been returned to AMU / Warehouse.",
                'detailed_message' => "Repair completed for {$propNo}{$itemName}. Returned to AMU ({$diffLabel}). Supplier: {$supName}.",
            ];

            $schoolId = $assignment ? $assignment->school_id : null;
            $admins = \App\Models\User::getNotificationRecipients($schoolId);
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AssetReturnedNotification($dummyAsset));
            }
        });

        return redirect()->route('asset.service.index')->with('success', 'Asset has been returned to AMU and marked as Good Condition!');
    }

    /**
     * Calculate working days between two dates using the Philippine calendar.
     * Returns negative if returned early, positive if late, 0 if on time.
     */
    private function getPhWorkingDaysDiff(Carbon $expected, Carbon $actual): int
    {
        if ($expected->isSameDay($actual)) {
            return 0;
        }

        $isLate    = $actual->greaterThan($expected);
        $startDate = $isLate ? $expected->copy()->addDay() : $actual->copy()->addDay();
        $endDate   = $isLate ? $actual->copy() : $expected->copy();

        // Static Philippine national non-working holidays (MM-DD format)
        $phHolidays = [
            '01-01', // New Year's Day
            '02-25', // EDSA People Power Anniversary
            '04-09', // Araw ng Kagitingan
            '05-01', // Labor Day
            '06-12', // Independence Day
            '08-21', // Ninoy Aquino Day
            '11-01', // All Saints' Day
            '11-02', // All Souls' Day (Special Non-Working)
            '11-30', // Bonifacio Day
            '12-08', // Feast of the Immaculate Conception
            '12-24', // Christmas Eve
            '12-25', // Christmas Day
            '12-30', // Rizal Day
            '12-31', // Last Day of the Year
        ];

        $workingDays = 0;
        while ($startDate->lessThanOrEqualTo($endDate)) {
            if (!$startDate->isWeekend() && !in_array($startDate->format('m-d'), $phHolidays)) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $isLate ? $workingDays : -$workingDays;
    }
}
