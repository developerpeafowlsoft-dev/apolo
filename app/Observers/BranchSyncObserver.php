<?php

namespace App\Observers;

use App\Models\SyncQueue;
use App\Services\BranchContext;
use Illuminate\Database\Eloquent\Model;

class BranchSyncObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $branchContext = app(BranchContext::class);

        if ($branchContext->isBranch()) {
            SyncQueue::create([
                'syncable_type' => get_class($model),
                'syncable_id' => $model->id,
                'payload' => $model->toArray(),
                'status' => 'pending',
                'attempts' => 0,
                'branch_id' => $branchContext->getCurrentBranchId(),
            ]);
        }
    }
}
