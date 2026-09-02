<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (config('branch.mode') === 'branch') {
            $schedule->call(function () {
                $pending = \App\Models\SyncQueue::whereIn('status', ['pending', 'failed'])
                    ->where('attempts', '<', 10)
                    ->get();
                foreach ($pending as $item) {
                    dispatch(new \App\Jobs\PushToCentralSync($item));
                }
            })->everyMinute();
        }

        if (config('branch.mode') === 'central') {
            $schedule->call(function () {
                $cutoff = now()->subHours(24);
                $unresolvedBranches = \App\Models\Branch::where('is_active', true)
                    ->where(function ($q) use ($cutoff) {
                        $q->whereNull('last_synced_at')
                          ->orWhere('last_synced_at', '<', $cutoff);
                    })
                    ->get();

                if ($unresolvedBranches->isNotEmpty()) {
                    $branchNames = $unresolvedBranches->pluck('name')->implode(', ');
                    
                    $admins = \App\Models\User::whereHas('roles', function ($q) {
                        $q->whereIn('name', ['root', 'admin']);
                    })->get();

                    $tokens = [];
                    foreach ($admins as $admin) {
                        $tokens = array_merge($tokens, $admin->devices()->pluck('key')->toArray());
                    }

                    if (!empty($tokens)) {
                        \App\Services\NotificationServices::sendNotification(
                            "The following branches have not synced in over 24 hours: " . $branchNames,
                            $tokens,
                            "Unsynced Branch Alert"
                        );
                    }
                }
            })->daily();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
