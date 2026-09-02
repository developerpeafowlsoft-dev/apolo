<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\POS\PosPaymentReconciliationService;
use App\Models\PosPaymentAttempt;
use App\Enums\PosPaymentAttemptStatus;

class ReconcilePosPaymentsCommand extends Command
{
    protected $signature = 'pos:reconcile-payments {--shop= : Specific Shop ID} {--date= : Reconciliation Date YYYY-MM-DD}';
    protected $description = 'Reconcile terminal payment attempts and recover unknown statuses';

    public function handle(PosPaymentReconciliationService $service): int
    {
        $this->info("Starting POS terminal payment reconciliation...");

        $shopId = $this->option('shop');
        $date = $this->option('date') ?: date('Y-m-d');

        $query = PosPaymentAttempt::where('status', PosPaymentAttemptStatus::UNKNOWN);
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        $unknownAttempts = $query->get();
        $this->info("Found " . $unknownAttempts->count() . " UNKNOWN payment attempts to recheck.");

        foreach ($unknownAttempts as $attempt) {
            $res = $service->recoverUnknownAttempt($attempt);
            $this->line("Attempt {$attempt->id}: " . ($res['status'] ?? 'unknown'));
        }

        $shops = $shopId ? [\App\Models\Shop::find($shopId)] : \App\Models\Shop::all();
        foreach ($shops as $shop) {
            if (!$shop) continue;
            $reconciliation = $service->reconcileDailyTerminalPayments($shop->id, $date);
            $this->info("Reconciliation for Shop {$shop->name} ({$date}): Total Sales ₹{$reconciliation->total_sales_amount}, Unknown Discrepancies: {$reconciliation->discrepancy_count}");
        }

        $this->info("Reconciliation process completed successfully.");
        return 0;
    }
}
