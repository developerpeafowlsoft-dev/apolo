<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\SyncQueue;
use App\Models\Branch;

class BranchHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report health status and connectivity check for this branch server.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branchMode = config('branch.mode');
        $branchCode = config('branch.code');

        if ($branchMode !== 'branch') {
            $this->error('This server is not configured in branch mode.');
            return 1;
        }

        $pendingCount = SyncQueue::where('status', 'pending')->count();
        $failedCount = SyncQueue::where('status', 'failed')->count();
        $syncedCount = SyncQueue::where('status', 'synced')->count();

        $branch = Branch::where('branch_code', $branchCode)->first();
        $lastSyncedAt = $branch && $branch->last_synced_at 
            ? $branch->last_synced_at->toDateTimeString() 
            : 'Never';

        $centralUrl = env('CENTRAL_API_URL', 'https://cloud.readyecommerce.com/api');
        $connectionStatus = 'Disconnected';

        try {
            $ping = Http::timeout(3)->head($centralUrl . '/ping');
            if ($ping->successful()) {
                $connectionStatus = 'Connected';
            }
        } catch (\Exception $e) {
            $connectionStatus = 'Disconnected (Error: ' . $e->getMessage() . ')';
        }

        $this->table(['Metric', 'Status/Value'], [
            ['Branch Code', $branchCode ?: 'Not Set'],
            ['Central URL', $centralUrl],
            ['Connectivity', $connectionStatus],
            ['Last Successful Sync', $lastSyncedAt],
            ['Pending Sync Items', $pendingCount],
            ['Failed Sync Items', $failedCount],
            ['Total Synced Items', $syncedCount],
        ]);

        return 0;
    }
}
