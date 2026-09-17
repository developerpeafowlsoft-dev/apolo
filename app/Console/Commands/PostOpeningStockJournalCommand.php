<?php

namespace App\Console\Commands;

use App\Services\OpeningStock\OpeningStockJournalService;
use Illuminate\Console\Command;
use Exception;

class PostOpeningStockJournalCommand extends Command
{
    protected $signature = 'opening-stock:journal
                            {--as-of=2026-04-01 : Journal date (first day of the FY the stock opens into)}
                            {--basis=net : Valuation basis - "net" (cost after trade discount) or "gross" (list purchase rate)}
                            {--shop=14 : Target Showroom Shop ID}
                            {--dry-run : Report the entry without writing it}
                            {--replace : Reverse an already-posted journal by contra and post a corrected one}';

    protected $description = 'Post the one-time opening journal: Dr Opening Stock / Cr Opening Balance Equity';

    public function handle(OpeningStockJournalService $service): int
    {
        $asOf   = (string)$this->option('as-of');
        $basis  = (string)$this->option('basis');
        $shopId = (int)$this->option('shop');
        $dryRun = (bool)$this->option('dry-run');

        $this->info(str_repeat('=', 78));
        $this->info('                 APOLO OPENING STOCK JOURNAL                 ');
        $this->info(str_repeat('=', 78));
        $this->line('Mode  : ' . ($dryRun
            ? '<fg=yellow;options=bold>DRY-RUN (no database writes)</>'
            : '<fg=green;options=bold>LIVE WRITE</>'));
        $this->newLine();

        try {
            $suffix = '';

            // Re-importing opening stock changes its valuation, which leaves an
            // already-posted journal overstating or understating the books. The
            // stale voucher is reversed by contra rather than deleted or edited:
            // in double entry you never un-post an entry, you post its opposite,
            // and the contra is what every report already nets against.
            if ($this->option('replace') && !$dryRun) {
                $voucherNo = 'JV-OS-' . str_replace('-', '', $asOf);
                $stale = \App\Models\Voucher::where('shop_id', $shopId)
                    ->where('voucher_no', 'like', $voucherNo . '%')
                    ->where('status', 'posted')
                    ->whereNull('reverses_voucher_id')
                    ->orderByDesc('id')
                    ->first();

                if ($stale) {
                    $contra = app(\App\Services\Accounting\VoucherService::class)
                        ->reverse($stale->id, $asOf, 'Reversal of ' . $stale->voucher_no
                            . ' - opening stock re-imported with per-challan costing');
                    $this->warn("  Reversed {$stale->voucher_no} by contra {$contra->voucher_no}.");
                    // The original keeps its number, so the corrected journal needs
                    // its own; the unique index is (shop_id, voucher_no).
                    $suffix = '-R' . (\App\Models\Voucher::where('shop_id', $shopId)
                        ->where('voucher_no', 'like', $voucherNo . '-R%')->count() + 2);
                } else {
                    $this->line('  --replace given but no posted journal found; posting fresh.');
                }
            }

            $r = $service->post($asOf, $basis, $dryRun, $shopId, $suffix);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        if ($r['already_posted']) {
            $this->warn("Journal {$r['voucher_no']} is already posted (voucher id {$r['voucher_id']}). Nothing to do.");
            return 0;
        }

        $amount = '₹ ' . number_format($r['amount'], 2);

        $this->table(['Field', 'Value'], [
            ['Voucher no', $r['voucher_no']],
            ['Date', $r['date'] . '   (FY id ' . $r['financial_year_id'] . ')'],
            ['Valuation basis', $r['basis'] === 'net' ? 'net purchase cost' : 'gross purchase rate'],
            ['Units', number_format($r['units'])],
            ['', ''],
            ['Dr  ' . ($r['debit_account'] ?? ('account #' . ($r['debit_account_id'] ?? '?'))), $amount],
            ['    Cr  ' . ($r['credit_account'] ?? ('account #' . ($r['credit_account_id'] ?? '?'))), $amount],
        ]);

        if (!empty($r['regrouped_account'])) {
            $this->warn('Chart of accounts corrected: ' . $r['regrouped_account']);
        }
        foreach ($r['created_accounts'] as $c) {
            $this->line("  <fg=cyan>created account:</> {$c}");
        }

        if ($dryRun) {
            $this->warn('Dry-run only - nothing was written. Re-run without --dry-run to post.');
        } else {
            $this->info("Posted. Voucher id {$r['voucher_id']}, balanced Dr = Cr = {$amount}.");
        }

        return 0;
    }
}
