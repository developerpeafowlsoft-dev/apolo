<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PosPaymentAttempt;
use App\Services\POS\PosPaymentReconciliationService;

class RecheckUnknownTerminalPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $attempt;

    public function __construct(PosPaymentAttempt $attempt)
    {
        $this->attempt = $attempt;
    }

    public function handle(PosPaymentReconciliationService $service): void
    {
        $service->recoverUnknownAttempt($this->attempt);
    }
}
