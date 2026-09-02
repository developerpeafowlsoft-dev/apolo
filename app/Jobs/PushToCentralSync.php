<?php

namespace App\Jobs;

use App\Models\SyncQueue;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Exception;

class PushToCentralSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SyncQueue $syncQueueItem;

    /**
     * Create a new job instance.
     */
    public function __construct(SyncQueue $syncQueueItem)
    {
        $this->syncQueueItem = $syncQueueItem;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->syncQueueItem->status === 'synced' || $this->syncQueueItem->attempts >= 10) {
            return;
        }

        $this->syncQueueItem->status = 'syncing';
        $this->syncQueueItem->save();

        $centralUrl = env('CENTRAL_API_URL', 'https://cloud.readyecommerce.com/api');
        $apiKey = env('BRANCH_API_KEY', 'default-key');

        try {
            $ping = Http::timeout(3)->head($centralUrl . '/ping');
            if (!$ping->successful()) {
                throw new Exception('Central API ping failed.');
            }

            $response = Http::timeout(10)
                ->withHeaders(['X-Branch-API-Key' => $apiKey])
                ->post($centralUrl . '/ingest', [
                    'syncable_type' => $this->syncQueueItem->syncable_type,
                    'syncable_id' => $this->syncQueueItem->syncable_id,
                    'payload' => $this->syncQueueItem->payload,
                    'branch_code' => env('BRANCH_CODE'),
                ]);

            if ($response->successful()) {
                $this->syncQueueItem->status = 'synced';
                $this->syncQueueItem->attempts = $this->syncQueueItem->attempts + 1;
                $this->syncQueueItem->last_attempted_at = now();
                $this->syncQueueItem->save();

                $branch = Branch::find($this->syncQueueItem->branch_id);
                if ($branch) {
                    $branch->last_synced_at = now();
                    $branch->save();
                }
            } else {
                throw new Exception('Central ingestion response error: ' . $response->status());
            }

        } catch (Exception $e) {
            $attempts = $this->syncQueueItem->attempts + 1;
            $this->syncQueueItem->attempts = $attempts;
            $this->syncQueueItem->last_attempted_at = now();

            if ($attempts >= 10) {
                $this->syncQueueItem->status = 'failed';
                $this->syncQueueItem->save();
            } else {
                $this->syncQueueItem->status = 'pending';
                $this->syncQueueItem->save();

                $delaySeconds = min($attempts * 60, 1800);
                dispatch(new self($this->syncQueueItem))->delay($delaySeconds);
            }
        }
    }
}
