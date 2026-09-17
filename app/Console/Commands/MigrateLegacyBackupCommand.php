<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Migration\LegacyDataMigrationService;
use Exception;

class MigrateLegacyBackupCommand extends Command
{
    protected $signature = 'migrate:legacy-backup 
                            {--step=all : Step number 1-15 or "all"} 
                            {--year= : Specific financial year folder (e.g. 2021, 2022, 2026, or all)} 
                            {--dry-run : Validate and parse files without writing to database} 
                            {--shop=14 : Target Showroom Shop ID}
                            {--correct-accounts : Automatically calibrate and reconcile all accounts and voucher balances}';

    protected $description = 'Migrate Legacy Windows ERP Excel Backup files into Apolo database';

    protected LegacyDataMigrationService $migrationService;

    public function __construct(LegacyDataMigrationService $migrationService)
    {
        parent::__construct();
        $this->migrationService = $migrationService;
    }

    public function handle(): int
    {
        $stepArg = $this->option('step');
        $yearArg = $this->option('year');
        $dryRun = (bool)$this->option('dry-run');
        $shopId = (int)$this->option('shop');
        $correctAccountsOpt = (bool)$this->option('correct-accounts');

        if (!empty($yearArg)) {
            $this->migrationService->setSelectedYear($yearArg);
        }

        $this->info("================================================================================");
        $this->info("             APOLO LEGACY WINDOWS ERP DATA MIGRATION ENGINE                     ");
        $this->info("================================================================================");
        $this->line("Mode: " . ($dryRun ? "<fg=yellow;options=bold>DRY-RUN (No database modifications)</>" : "<fg=green;options=bold>LIVE WRITE (Database will be updated)</>"));
        $this->line("Financial Year Scope: <fg=magenta;options=bold>" . ($this->migrationService->getSelectedYear() ?: 'All Available / Root') . "</>");
        $this->line("Target Shop ID: <fg=cyan>{$shopId}</>");
        $this->line("Backup Directory: <fg=gray>{$this->migrationService->getBackupDir()}</>");
        $this->newLine();

        if ($correctAccountsOpt && $stepArg === 'all' && !$this->input->hasParameterOption('--step')) {
            $this->info(">>> Running Auto Account Correction & Reconciliation...");

            if (!$dryRun && !$this->confirmAccountCorrection($shopId, $yearArg)) {
                return 1;
            }

            $accResult = $this->migrationService->autoCorrectAccounts($shopId, $yearArg, $dryRun);
            $this->renderAccountResult($accResult);
            $this->info(($dryRun ? "Dry run finished in " : "Account correction completed in ") . $accResult['duration_seconds'] . "s.");
            return 0;
        }


        $stepsDef = $this->migrationService->getStepsDefinition();
        $totalStepCount = count($stepsDef);

        $stepsToRun = [];
        if ($stepArg === 'all') {
            $stepsToRun = array_keys($stepsDef);
        } elseif (is_numeric($stepArg) && isset($stepsDef[(int)$stepArg])) {
            $stepsToRun = [(int)$stepArg];
        } else {
            $this->error("Invalid --step option. Please specify a step from 1 to {$totalStepCount} or 'all'.");
            return 1;
        }

        $resultsTable = [];
        $totalDuration = 0;
        $totalRead = 0;
        $totalInserted = 0;

        foreach ($stepsToRun as $step) {
            $def = $stepsDef[$step];
            $this->info(">>> Processing Step {$step}/{$totalStepCount}: {$def['name']} ({$def['file']})...");

            try {
                $res = $this->migrationService->runStep($step, $dryRun, $shopId, function($current, $total) {
                    // Optional progress indicator
                });

                $totalDuration += $res['duration_seconds'];
                $totalRead += $res['total_read'];
                $totalInserted += $res['count'];

                $statusText = $dryRun ? "<fg=yellow>VALIDATED</>" : "<fg=green>MIGRATED</>";
                $this->line("    ✓ Finished: {$res['count']} records processed in {$res['duration_seconds']}s");

                $resultsTable[] = [
                    'Step' => $step,
                    'Module' => $def['name'],
                    'File' => $res['file'] ?? $def['file'],
                    'Target Table' => $def['table'],
                    'Rows Read' => number_format($res['total_read']),
                    'Records Count' => number_format($res['count']),
                    'Time (s)' => $res['duration_seconds'],
                    'Status' => $dryRun ? 'Validated (Dry-Run)' : 'Success'
                ];
            } catch (Exception $e) {
                $this->error("    ✗ Failed Step {$step}: " . $e->getMessage());
                $resultsTable[] = [
                    'Step' => $step,
                    'Module' => $def['name'],
                    'File' => $def['file'],
                    'Target Table' => $def['table'],
                    'Rows Read' => '0',
                    'Records Count' => '0',
                    'Time (s)' => '0.0',
                    'Status' => 'Error: ' . substr($e->getMessage(), 0, 30)
                ];
            }
        }

        $this->newLine();
        $this->table(['Step', 'Module', 'Excel File', 'Target Table', 'Rows Read', 'Records Processed', 'Time (s)', 'Status'], $resultsTable);
        $this->newLine();

        // Print Barcode Lifecycle & Stock Reconciliation Summary
        if (in_array(13, $stepsToRun) || in_array(15, $stepsToRun) || $stepArg === 'all') {
            $recon = $this->migrationService->getBarcodeReconciliation($this->migrationService->getSelectedYear(), $shopId);
            $this->info("================================================================================");
            $this->info("             BARCODE INVENTORY & SALES USAGE RECONCILIATION REPORT              ");
            $this->info("================================================================================");

            $this->table(
                ['Metric', 'Count', 'Description / Accounting Note'],
                [
                    ['Total Barcodes Generated (Inward)', number_format($recon['total_generated']), 'All unique physical barcodes registered into stock'],
                    ['Total Barcodes Sold (POS Sales)', number_format($recon['total_sold']), 'Barcodes consumed by customer retail invoices'],
                    ['Active In-Stock Barcodes (Available)', number_format($recon['total_in_stock']), 'Physical stock remaining on retail shelves'],
                    ['POS Sales Item Lines Processed', number_format($recon['total_pos_sales_rows']), 'Total positive sales lines from sales vouchers'],
                    ['POS Customer Return Lines Processed', number_format($recon['total_pos_returns_rows']), 'Returned garments restocked back into store inventory'],
                    ['Reconciliation Balance Status', $recon['reconciliation_diff'] === 0 ? '<fg=green;options=bold>100% PERFECTLY BALANCED</>' : '<fg=yellow>Diff: ' . $recon['reconciliation_diff'] . '</>', 'Total Generated = Total Sold + Total In-Stock']
                ]
            );

            if (!empty($recon['year_breakdown'])) {
                $this->newLine();
                $this->info("Financial Year Backup Folders Breakdown:");
                $yRows = [];
                foreach ($recon['year_breakdown'] as $yr => $data) {
                    $yRows[] = [
                        $yr,
                        $data['has_barcode_sheet'] ? '<fg=green>Yes</> (' . $data['barcode_file'] . ')' : '<fg=red>No</>',
                        $data['has_sales_sheet'] ? '<fg=green>Yes</>' : '<fg=red>No</>',
                        $data['has_inward_sheet'] ? '<fg=green>Yes</>' : '<fg=red>No</>',
                        $data['has_purchase_sheet'] ? '<fg=green>Yes</>' : '<fg=red>No</>',
                    ];
                }
                $this->table(['Year', 'Barcode Sheet', 'Sales Sheet', 'Inward Sheet', 'Purchase Sheet'], $yRows);
            }
            $this->newLine();
        }

        if ($correctAccountsOpt) {
            $this->newLine();
            $this->info(">>> Post-Migration: Running Auto Account Correction & Reconciliation...");

            if (!$dryRun && !$this->confirmAccountCorrection($shopId, $yearArg)) {
                return 1;
            }

            $accResult = $this->migrationService->autoCorrectAccounts($shopId, $yearArg, $dryRun);
            $this->renderAccountResult($accResult);
        }

        $this->info("Migration summary: {$totalRead} rows read, {$totalInserted} records processed across " . count($stepsToRun) . " steps in {$totalDuration}s.");
        return 0;
    }


    /**
     * Account correction deletes every account_balances row for the shop and
     * rebuilds it from voucher entries alone. Balances imported from the Opening
     * Balance sheet that have no backing voucher are discarded, so show the real
     * cost - measured by a rolled-back dry run - before writing anything.
     */
    protected function confirmAccountCorrection(int $shopId, ?string $yearArg): bool
    {
        $preview = $this->migrationService->autoCorrectAccounts($shopId, $yearArg, true);
        $lost = (int)($preview['balances_lost'] ?? 0);

        $this->line("    Account balances now: <fg=cyan>{$preview['balances_before']}</>  ->  after: <fg=cyan>{$preview['balances_after']}</>");

        if ($lost > 0) {
            $this->newLine();
            $this->warn("  {$lost} account balance row(s) would be DISCARDED.");
            $this->line('  These have no backing voucher - typically opening balances imported from');
            $this->line('  <fg=gray>Account/Opening Balance.xlsx</>. Re-run <fg=cyan>--step=9</> afterwards to restore them.');
            $this->newLine();
        }

        return $this->confirm('Proceed and write these changes?', false);
    }

    protected function renderAccountResult(array $r): void
    {
        $isDry = !empty($r['dry_run']);

        if ($isDry) {
            $this->newLine();
            $this->warn('  DRY RUN - nothing below has been written.');
            $this->newLine();
        }

        $rows = [
            ['Vouchers Audited', number_format($r['vouchers_audited']), 'All store transaction vouchers'],
            ['Vouchers Balanced', number_format($r['vouchers_balanced']), 'Vouchers with rounding/balancing adjustments'],
            ['FY Mismatches Fixed', number_format($r['fy_mismatches_fixed']), 'Vouchers re-aligned to correct financial year'],
            ['Party Masters Synced', number_format($r['account_masters_synced']), 'Party ledgers mapped to chart of accounts'],
            ['Account Balances Updated', number_format($r['account_balances_updated']), 'Ledger balances recalculated across FYs'],
        ];

        if ($isDry) {
            $lost = (int)($r['balances_lost'] ?? 0);
            $rows[] = ['Account Balances Before', number_format($r['balances_before']), 'Rows present now'];
            $rows[] = ['Account Balances After', number_format($r['balances_after']), 'Rows a live run would leave'];
            $rows[] = [
                'Balances DISCARDED',
                $lost > 0 ? '<fg=red;options=bold>' . number_format($lost) . '</>' : '0',
                $lost > 0 ? 'No backing voucher - restore with --step=9' : 'Nothing would be lost',
            ];
        }

        $rows[] = ['Active Financial Year', $r['active_financial_year'], 'Current active operational financial year'];
        $rows[] = ['Grand Total Debit (Dr)', '₹ ' . number_format($r['grand_total_debit'], 2), 'Total debits across all ledger entries'];
        $rows[] = ['Grand Total Credit (Cr)', '₹ ' . number_format($r['grand_total_credit'], 2), 'Total credits across all ledger entries'];
        $rows[] = ['Net Difference (Dr - Cr)', '₹ ' . number_format($r['net_difference'], 2), 'Double-entry balance discrepancy'];
        $rows[] = [
            'Account Balance Status',
            $r['is_fully_balanced'] ? '<fg=green;options=bold>100% PERFECTLY BALANCED</>' : '<fg=red;options=bold>UNBALANCED</>',
            'Dr == Cr verification',
        ];

        $this->table(['Metric', 'Value', 'Audit Note'], $rows);

        if ($isDry) {
            $this->warn('  Dry run only. Re-run without --dry-run to apply.');
        }
    }
}
