<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\POS\PosPaymentReconciliationService;
use App\Models\Shop;

class ReconcileTerminalPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopId;
    public $date;

    public function __construct(?int $shopId = null, ?string $date = null)
    {
        $this->shopId = $shopId;
        $this->date = $date ?: date('Y-m-d');
    }

    public function handle(PosPaymentReconciliationService $service): void
    {
        $shops = $this->shopId ? Shop::where('id', $this->shopId)->get() : Shop::all();
        foreach ($shops as $shop) {
            $service->reconcileDailyTerminalPayments($shop->id, $this->date);
        }
    }
}
