<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Removes inward stock for one shop so an Opening Stock snapshot can be
 * re-imported cleanly. Scoped to a single shop - unlike the migration
 * dashboard's Clear buttons, which truncate across every shop.
 *
 * Refuses to run if any barcode has been sold or invoiced, so it can never
 * destroy trading history.
 */
class ResetOpeningStockCommand extends Command
{
    protected $signature = 'opening-stock:reset
                            {--shop=14 : Shop ID to clear}
                            {--force : Skip the confirmation prompt}
                            {--allow-sold : Proceed even though barcodes are flagged sold (see the note in handle())}';

    protected $description = 'Clear inward invoices, inward products and barcodes for one shop';

    public function handle(): int
    {
        $shopId = (int)$this->option('shop');

        $barcodes = DB::table('product_barcodes')->where('shop_id', $shopId)->count();
        $products = DB::table('inward_products')->where('shop_id', $shopId)->count();
        $invoices = DB::table('inward_invoices')->where('shop_id', $shopId)->count();

        if ($barcodes === 0 && $products === 0 && $invoices === 0) {
            $this->info("Shop {$shopId} already has no inward stock. Nothing to do.");
            return 0;
        }

        $this->table(['Table', 'Rows to delete'], [
            ['product_barcodes', number_format($barcodes)],
            ['inward_products', number_format($products)],
            ['inward_invoices', number_format($invoices)],
        ]);

        // --- safety guards: never destroy trading history --------------------
        // The sold flag normally means a real POS sale, and deleting the barcode
        // would take the only record of it. There is one case where it does not:
        // flags set by `legacy:reconcile-sales`, which are derived from the legacy
        // sale exports and can be rebuilt from them in a single run. --allow-sold
        // covers that case and nothing else, so the guard stays absolute by
        // default and the caller has to say out loud that the flags are derived.
        $sold = DB::table('product_barcodes')->where('shop_id', $shopId)->where('is_sold', 1)->count();
        if ($sold > 0 && !$this->option('allow-sold')) {
            $this->error("Refusing to run: {$sold} barcode(s) are marked sold.");
            $this->line('Clearing them would destroy sales history. Investigate before resetting.');
            $this->newLine();
            $this->line('  If these flags came from <fg=cyan>legacy:reconcile-sales</> they are derived, not');
            $this->line('  trading history, and can be rebuilt. Re-run with <fg=cyan>--allow-sold</> to proceed,');
            $this->line('  then restore them afterwards with:');
            $this->line('    <fg=cyan>artisan legacy:reconcile-sales --shop=' . $shopId . ' --force --skip-customers</>');
            return 1;
        }
        if ($sold > 0) {
            $this->warn("  --allow-sold given: {$sold} derived sold flag(s) will be cleared.");
            $this->line('  Restore with: artisan legacy:reconcile-sales --shop=' . $shopId . ' --force --skip-customers');
            $this->newLine();
        }

        $invoiced = DB::table('product_purchases')
            ->whereIn('inward_invoice_id', DB::table('inward_invoices')->where('shop_id', $shopId)->select('id'))
            ->count();
        if ($invoiced > 0) {
            $this->error("Refusing to run: {$invoiced} purchase bill(s) reference these inward invoices.");
            return 1;
        }

        if (!$this->option('force') && !$this->confirm("Delete the above from shop {$shopId}? A database backup should already exist.", false)) {
            $this->warn('Aborted. Nothing was deleted.');
            return 1;
        }

        $deleted = DB::transaction(function () use ($shopId) {
            return [
                'barcodes' => DB::table('product_barcodes')->where('shop_id', $shopId)->delete(),
                'products' => DB::table('inward_products')->where('shop_id', $shopId)->delete(),
                'invoices' => DB::table('inward_invoices')->where('shop_id', $shopId)->delete(),
            ];
        });

        $this->info(sprintf(
            'Deleted %s barcodes, %s inward products, %s inward invoices from shop %d.',
            number_format($deleted['barcodes']),
            number_format($deleted['products']),
            number_format($deleted['invoices']),
            $shopId
        ));

        return 0;
    }
}
