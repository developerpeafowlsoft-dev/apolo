<?php

namespace App\Console\Commands;

use App\Services\Migration\LegacySalesReconciliationService;
use Illuminate\Console\Command;

/**
 * Reconciles Opening Stock against the legacy sale history and imports the retail
 * customers that only ever existed as a name and mobile on a sale line.
 *
 * Defaults to a dry run. A live run asks for confirmation first, because both
 * halves write to tables the shop is already using.
 */
class ReconcileLegacySalesCommand extends Command
{
    protected $signature = 'legacy:reconcile-sales
        {--shop=14 : Shop id to reconcile}
        {--dry-run : Scan and report without writing anything}
        {--skip-sold : Do not touch product_barcodes}
        {--skip-customers : Do not create users or customers}
        {--force : Skip the confirmation prompt on a live run}
        {--report= : Write the full scan result to this JSON path}';

    protected $description = 'Flag Opening Stock barcodes already sold in the legacy books, and import legacy retail customers';

    public function handle(LegacySalesReconciliationService $service): int
    {
        $shopId = (int)$this->option('shop');
        $dryRun = (bool)$this->option('dry-run');

        $this->newLine();
        $this->line('  <options=bold>Legacy Sales Reconciliation</>');
        $this->line('  shop ' . $shopId . '   ' . ($dryRun
            ? '<fg=yellow;options=bold>DRY-RUN (no database writes)</>'
            : '<fg=red;options=bold>LIVE WRITE</>'));
        $this->newLine();

        $this->line('  Scanning legacy sale exports...');
        $started = microtime(true);

        $scan = $service->scan($shopId, function (string $year, array $info) {
            $this->line(sprintf('    %-11s %s rows', $year, number_format($info['data_rows'] ?? 0)));
        });

        $stats = $scan['stats'];
        $this->newLine();
        $this->line(sprintf(
            '  %s files, %s data rows (%s subtotal rows skipped) in %.1fs',
            number_format($stats['files']),
            number_format($stats['data_rows']),
            number_format($stats['subtotal_rows']),
            microtime(true) - $started
        ));
        $this->line(sprintf(
            '  %s sale rows, %s return rows, %s rows with no mobile',
            number_format($stats['sale_rows']),
            number_format($stats['return_rows']),
            number_format($stats['rows_without_mobile'])
        ));

        foreach ($stats['per_year'] as $year => $info) {
            if (($info['status'] ?? '') === 'missing') {
                $this->warn(sprintf('  %s has no detail sale export - its sales are not represented.', $year));
            }
        }

        $this->newLine();
        $this->line('  <options=bold>Opening Stock vs sale history</>');
        $this->table(
            ['', 'Barcodes'],
            [
                ['In stock (shop ' . $shopId . ')', number_format($stats['stock_barcodes'])],
                ['Seen in a sale file', number_format(count($scan['sold']) + count($scan['returned']))],
                ['  ...last event was a RETURN (stays available)', number_format(count($scan['returned']))],
                ['  ...last event was a SALE (flag as sold)', number_format(count($scan['sold']))],
            ]
        );

        if ($this->option('report')) {
            $this->writeReport((string)$this->option('report'), $scan);
        }

        if (!$dryRun && !$this->confirmLive($scan)) {
            $this->warn('  Aborted. Nothing was written.');
            return self::SUCCESS;
        }

        if (!$this->option('skip-sold')) {
            $r = $service->applySoldFlags($scan['sold'], $shopId, $dryRun);
            $this->newLine();
            $this->line('  <options=bold>Sold flags</>');
            $this->line(sprintf(
                '    %s resolved sold, %s already flagged, <fg=green>%s %s</>',
                number_format($r['candidates']),
                number_format($r['already_sold']),
                number_format($r['updated']),
                $dryRun ? 'would be updated' : 'updated'
            ));
        }

        if (!$this->option('skip-customers')) {
            $r = $service->importCustomers($scan['customers'], $dryRun);
            $this->newLine();
            $this->line('  <options=bold>Customers</>');
            $this->line(sprintf(
                '    %s distinct legacy mobiles, %s already in users',
                number_format($r['legacy_total']),
                number_format($r['existing'])
            ));
            $this->line(sprintf(
                '    <fg=green>%s %s</>, %s customer link(s) %s',
                number_format($r['created']),
                $dryRun ? 'would be created' : 'created',
                number_format($r['linked']),
                $dryRun ? 'would be added' : 'added'
            ));
        }

        $this->renderAnomalies($scan['anomalies']);

        $this->newLine();
        $this->info($dryRun
            ? '  Dry run complete - nothing was written.'
            : '  Reconciliation complete.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Both halves of this command write to live tables, so a live run states the
     * exact row counts and waits for a yes.
     */
    protected function confirmLive(array $scan): bool
    {
        $this->newLine();
        $this->warn('  This will write to the database:');
        if (!$this->option('skip-sold')) {
            $this->line('    - set is_sold = 1 on up to ' . number_format(count($scan['sold'])) . ' product_barcodes rows');
        }
        if (!$this->option('skip-customers')) {
            $this->line('    - create up to ' . number_format(count($scan['customers'])) . ' users + customers');
        }
        $this->newLine();

        if ($this->option('force')) {
            $this->line('  --force given, proceeding without a prompt.');
            return true;
        }

        return $this->confirm('  Proceed?', false);
    }

    protected function renderAnomalies(array $anomalies): void
    {
        if (empty($anomalies)) {
            return;
        }

        $byType = [];
        foreach ($anomalies as $a) {
            $byType[$a['type']][] = $a;
        }

        $this->newLine();
        $this->line('  <options=bold>Anomalies for review</>');
        foreach ($byType as $type => $rows) {
            $this->line(sprintf('    %-22s %s', $type, number_format(count($rows))));
            foreach (array_slice($rows, 0, 3) as $r) {
                $this->line('      <fg=gray>' . mb_substr($r['detail'], 0, 96) . '</>');
            }
            if (count($rows) > 3) {
                $this->line('      <fg=gray>... and ' . number_format(count($rows) - 3) . ' more (use --report)</>');
            }
        }
    }

    protected function writeReport(string $path, array $scan): void
    {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'stats' => $scan['stats'],
            'sold' => array_values($scan['sold']),
            'anomalies' => $scan['anomalies'],
            'customers_sample' => array_slice(array_map(
                fn ($c) => array_diff_key($c, ['names' => true]),
                $scan['customers']
            ), 0, 50),
        ];

        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line('  Report written to ' . $path);
    }
}
