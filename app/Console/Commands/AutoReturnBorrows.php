<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\AssetTransferNotification;

class AutoReturnBorrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-return-borrows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically return temporarily borrowed assets to their original owners when the return date matches or passes today.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        // Find temporary transfers that are due/overdue, where the asset's current custodian is still the borrower.
        // We also ensure this is the most recent transfer log for this asset assignment.
        $dueBorrows = DB::table('asset_transfers as at')
            ->join('asset_assignments as aa', 'at.asset_assignment_id', '=', 'aa.id')
            ->where('at.transfer_type', 'Temporary Borrow')
            ->whereNotNull('at.return_date')
            ->where('at.return_date', '<=', $today)
            ->whereColumn('aa.employee_id', '=', 'at.to_custodian_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('asset_transfers as at2')
                    ->whereColumn('at2.asset_assignment_id', 'at.asset_assignment_id')
                    ->whereColumn('at2.created_at', '>', 'at.created_at');
            })
            ->select('at.*', 'aa.property_number', 'aa.asset_source_id')
            ->get();

        if ($dueBorrows->isEmpty()) {
            $this->info('No due temporary borrows found.');
            return 0;
        }

        $count = 0;
        foreach ($dueBorrows as $borrow) {
            DB::transaction(function () use ($borrow, $today) {
                // 1. Update Asset Assignment back to original custodian
                DB::table('asset_assignments')->where('id', $borrow->asset_assignment_id)->update([
                    'employee_id' => $borrow->from_custodian_id,
                    'acquisition_date' => $today,
                    'updated_at' => now(),
                ]);

                // 2. Log the Transfer Back in asset_transfers
                DB::table('asset_transfers')->insert([
                    'asset_assignment_id' => $borrow->asset_assignment_id,
                    'from_office_id' => $borrow->to_office_id,
                    'to_office_id' => $borrow->from_office_id,
                    'from_custodian_id' => $borrow->to_custodian_id,
                    'to_custodian_id' => $borrow->from_custodian_id,
                    'transfer_date' => $today,
                    'transfer_type' => 'Return',
                    'remarks' => 'Automatic return: Temporary borrow period expired (due: ' . $borrow->return_date . ').',
                    'authorized_by' => 1, // System default authorization ID
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Send Notification to Admins
                $source = DB::table('asset_sources')->where('id', $borrow->asset_source_id)->first();
                $itemName = $source ? DB::table('items')->where('id', $source->item_id)->value('name') : 'Unknown Item';
                $uom = $source->unit_of_measurement ?? 'Unit';
                $qty = $source->quantity ?? 1;
                $propNoStr = $borrow->property_number ? '[' . $borrow->property_number . '] ' : '';
                
                $borrowerName = DB::table('employees')->where('id', $borrow->to_custodian_id)->value(DB::raw("CONCAT(first_name, ' ', last_name)")) ?: 'Unknown';
                $ownerName = DB::table('employees')->where('id', $borrow->from_custodian_id)->value(DB::raw("CONCAT(first_name, ' ', last_name)")) ?: 'Warehouse';

                $detailedMessage = "Automatically returned {$qty} {$uom} {$propNoStr}{$itemName} from {$borrowerName} back to original custodian {$ownerName} (Borrow period expired).";

                /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\User> $admins */
                $admins = User::query()->where('approved', true)->get();
                $notificationData = (object)[
                    'title' => 'Asset Auto-Returned',
                    'message' => 'A temporarily borrowed asset has been automatically returned.',
                    'detailed_message' => $detailedMessage
                ];
                foreach ($admins as $admin) {
                    $admin->notify(new AssetTransferNotification($notificationData));
                }
            });
            $count++;
        }

        $this->info("Successfully returned {$count} borrowed assets.");
        return 0;
    }
}
