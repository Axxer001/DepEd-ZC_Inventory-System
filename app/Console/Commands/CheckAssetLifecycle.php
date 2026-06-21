<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAssetLifecycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-asset-lifecycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assets = \Illuminate\Support\Facades\DB::table('asset_sources')
            ->where('estimated_useful_life', '>', 0)
            ->whereNotNull('acceptance_date')
            ->get();

        $thresholds = [50, 25, 10, 5, 1];
        $today = now()->startOfDay();
        $admins = \App\Models\User::where('approved', true)->get();

        foreach ($assets as $asset) {
            $acceptanceDate = \Carbon\Carbon::parse($asset->acceptance_date)->startOfDay();
            $totalDays = $asset->estimated_useful_life * 365.25;

            foreach ($thresholds as $percent) {
                $daysElapsedToThreshold = round($totalDays * (1 - ($percent / 100)));
                $thresholdDate = $acceptanceDate->copy()->addDays($daysElapsedToThreshold);
                $remainingDays = round($totalDays - $daysElapsedToThreshold);

                if ($today->equalTo($thresholdDate)) {
                    $dummyAsset = (object)[
                        'title' => "Lifecycle Alert: {$percent}% remaining",
                        'message' => "Asset '{$asset->description}' has reached {$percent}% of its useful life.",
                        'detailed_message' => "The asset with Property Number '{$asset->property_number}' and Description '{$asset->description}' has {$percent}% ({$remainingDays} days) of its {$asset->estimated_useful_life} months useful life remaining."
                    ];
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\AssetLifecycleNotification($dummyAsset));
                    }
                }
            }
        }
        $this->info('Asset lifecycle check completed.');
    }
}
