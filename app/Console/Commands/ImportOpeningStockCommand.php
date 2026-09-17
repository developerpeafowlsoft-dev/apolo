<?php

namespace App\Console\Commands;

use App\Services\OpeningStock\OpeningStockImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Exception;

class ImportOpeningStockCommand extends Command
{
    protected $signature = 'opening-stock:import
                            {--year=2025-2026 : Year folder holding the closing stock snapshot}
                            {--as-of=2026-03-31 : Opening stock date}
                            {--shop=14 : Target Showroom Shop ID}
                            {--dry-run : Parse and report without writing to the database}
                            {--force : Skip the backup confirmation prompt}
                            {--all-years : Load every yearly snapshot, deduplicated, instead of the closing one}';

    protected $description = 'Import a legacy Barcode Search snapshot as Opening Stock, preserving physical barcodes';

    public function handle(OpeningStockImportService $service): int
    {
        $year   = (string)$this->option('year');
        $asOf   = (string)$this->option('as-of');
        $shopId = (int)$this->option('shop');
        $dryRun = (bool)$this->option('dry-run');

        $this->info(str_repeat('=', 78));
        $this->info('                 APOLO OPENING STOCK IMPORT                 ');
        $this->info(str_repeat('=', 78));
        $this->line('Mode      : ' . ($dryRun
            ? '<fg=yellow;options=bold>DRY-RUN (no database writes)</>'
            : '<fg=green;options=bold>LIVE WRITE</>'));
        $this->line("Snapshot  : <fg=magenta>{$year}</>");
        $this->line("As at     : <fg=magenta>{$asOf}</>");
        $this->line("Shop ID   : <fg=cyan>{$shopId}</>");
        $this->newLine();

        if (!$dryRun) {
            if (!Schema::hasColumn('inward_invoices', 'is_opening_stock')) {
                $this->error('Missing column inward_invoices.is_opening_stock.');
                $this->line('Run <fg=cyan>php artisan migrate</> first, then retry.');
                return 1;
            }

            if ($this->option('force')) {
                $this->warn('  --force given: proceeding without the backup prompt.');
            } elseif (!$this->confirm("This writes opening stock into shop {$shopId}. Have you taken a database backup?", false)) {
                $this->warn('Aborted. Re-run with --dry-run first, or take a backup.');
                return 1;
            }
        }

        try {
            $r = $service->import($year, $asOf, $dryRun, $shopId, function ($stage, $value) {
                $line = match ($stage) {
                    'index' => '  ' . $value,
                    'scan'  => '  streamed ' . number_format((int)$value) . ' barcodes...',
                    'write' => '  written ' . number_format((int)$value) . ' challans...',
                    default => '',
                };
                $this->output->write("\r" . str_pad($line, 64));
            }, (bool)$this->option('all-years'));
            $this->output->write("\r" . str_repeat(' ', 64) . "\r");
        } catch (Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        // How many barcodes found their inward challan, and how firmly.
        $this->newLine();
        $this->line('  <options=bold>Inward challans read</>');
        $rows = [];
        foreach ($r['inward_index'] as $fy => $ix) {
            $rows[] = [$fy, $ix['shape'], number_format($ix['vouchers']), number_format($ix['lines'])];
        }
        $this->table(['FY', 'Shape', 'Vouchers', 'Item lines'], $rows);

        if (!empty($r['per_snapshot']) && count($r['per_snapshot']) > 1) {
            $this->line('  <options=bold>Barcodes taken per snapshot</> <fg=gray>(first sighting wins)</>');
            $snap = [];
            foreach ($r['per_snapshot'] as $y => $n) {
                $snap[] = [$y, number_format($n)];
            }
            $this->table(['Snapshot', 'New barcodes'], $snap);
            $this->line('  <fg=gray>' . number_format($r['duplicate_rows'] ?? 0) . ' rows skipped as already loaded from an earlier year</>');
            $this->newLine();
        }

        $this->line('  <options=bold>Barcode to challan</>');
        $res = $r['resolution'];
        $tot = max(1, array_sum($res));
        $this->table(['Resolution', 'Barcodes', 'Share'], [
            ['Voucher + party matched', number_format($res['voucher+party']), sprintf('%.1f%%', $res['voucher+party'] / $tot * 100)],
            ['Voucher matched, party differs', number_format($res['voucher']), sprintf('%.1f%%', $res['voucher'] / $tot * 100)],
            ['No challan - snapshot only', number_format($res['unresolved']), sprintf('%.1f%%', $res['unresolved'] / $tot * 100)],
        ]);

        arsort($r['line_match']);
        $lm = [];
        foreach ($r['line_match'] as $label => $n) {
            $lm[] = [$label, number_format($n), sprintf('%.1f%%', $n / $tot * 100)];
        }
        $this->line('  <options=bold>Barcode to challan line</>');
        $this->table(['Matched on', 'Barcodes', 'Share'], $lm);

        $this->table(['Metric', 'Value'], [
            ['Source file', $r['source_file']],
            ['Rows read', number_format($r['total_read'])],
            ['', ''],
            ['Inward invoices ' . ($dryRun ? '(planned)' : 'created'), number_format($dryRun ? $r['planned_invoices'] : $r['invoices'])],
            ['Inward product lines ' . ($dryRun ? '(planned)' : 'created'), number_format($dryRun ? $r['planned_products'] : $r['products'])],
            ['Barcodes ' . ($dryRun ? '(planned)' : 'imported'), number_format($dryRun ? $r['planned_barcodes'] : $r['barcodes'])],
            ['Barcodes skipped (already present)', number_format($r['skipped_existing'])],
            ['', ''],
            ['Rows with Qty <> 1', number_format($r['anomalies']['qty_not_one']) . '  (imported as 1 unit each)'],
            ['Rows missing Design No', number_format($r['anomalies']['missing_design']) . '  (resolved by item name)'],
            ['Rows missing Party', number_format($r['anomalies']['missing_party']) . '  (assigned to Opening Stock Supplier)'],
            ['Duration', $r['duration_seconds'] . 's'],
        ]);

        if ($dryRun) {
            $this->warn('Dry-run only - nothing was written. Re-run without --dry-run to apply.');
        } else {
            $this->info('Opening stock imported. Next: post the opening journal (Dr Opening Stock / Cr Opening Stock Equity).');
        }

        return 0;
    }
}
