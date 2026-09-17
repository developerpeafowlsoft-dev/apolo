<?php

namespace App\Services\OpeningStock;

use App\Services\Migration\LegacyDataMigrationService;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Imports the legacy stock position at 31 March 2026 as Opening Stock.
 *
 * Two sources, joined:
 *
 *   Barcode Search P.xlsx            one row per physical barcode still in stock.
 *                                    Carries the item, design, colour, size, HSN,
 *                                    MRP, the four purchase-cost components and
 *                                    markup/markdown - plus the inward voucher
 *                                    number and date that brought the item in.
 *
 *   Voucher Detail Wise INWARD P     one per financial year. Carries the challan
 *                                    itself: supplier, GSTIN, sales rate, tax
 *                                    name and the SGST/CGST/IGST split.
 *
 * The snapshot alone cannot say what a garment cost in tax terms or what it was
 * meant to sell for, and the challans alone cannot say what is still on the
 * shelf. Joining them on (financial year, voucher no, party) reconstructs the
 * full position. The year matters: legacy voucher numbers restart at 00001 each
 * April, so a number on its own is ambiguous ten times over.
 *
 * Invoices are grouped by real challan rather than by supplier, so an
 * inward_invoices row corresponds to something that actually happened.
 *
 * Where a challan cannot be found the barcode is still imported, with its
 * snapshot attributes and legacy_source = 'snapshot_only'. Three situations
 * produce that, and all three are data facts rather than failures:
 *   - the item was opening stock in the LEGACY system too (Day Book = OPENING
 *     STOCK, placeholder voucher 00001, ~3,100 barcodes)
 *   - its inward year predates the exports (FY 2015-16 and 2016-17)
 *   - its year's export is the known 2024-25 duplicate of 2020-21
 */
class OpeningStockImportService
{
    /** Financial years whose inward exports are searched for challans. */
    protected const INWARD_YEARS = [
        '2016-2017', '2017-2018', '2018-2019', '2019-2020', '2020-2021',
        '2021-2022', '2022-2023', '2023-2024', '2024-2025', '2025-2026',
    ];

    public function __construct(
        protected LegacyDataMigrationService $legacy,
        protected LegacyInwardIndex $inward
    ) {
    }

    public function import(
        string $sourceYear,
        string $asOfDate,
        bool $dryRun = true,
        int $shopId = 14,
        ?callable $progress = null,
        bool $allYears = false
    ): array {
        ini_set('memory_limit', '2048M');
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        // Default: the closing snapshot alone, which is the stock position at the
        // cutover date. --all-years instead walks every year in order and keeps the
        // first sighting of each barcode, giving one row per garment the shop has
        // ever owned. The later sales pass then flags the ones that left.
        if ($allYears) {
            $paths = [];
            foreach (self::INWARD_YEARS as $y) {
                $p = $this->snapshotPath($y);
                if ($p !== null) {
                    $paths[$y] = $p;
                }
            }
            if (empty($paths)) {
                throw new Exception('No barcode snapshots found in backup_excel/.');
            }
        } else {
            $p = $this->snapshotPath($sourceYear);
            if ($p === null) {
                throw new Exception("No barcode snapshot found in backup_excel/{$sourceYear}/");
            }
            $paths = [$sourceYear => $p];
        }
        $path = end($paths);

        $startTime = microtime(true);

        if ($progress) {
            $progress('index', 'Reading inward challans across ' . count(self::INWARD_YEARS) . ' years...');
        }
        $indexReport = $this->inward->build(self::INWARD_YEARS);

        [$invoices, $stats] = $this->collect($paths, $progress);

        $summary = [
            'source_file' => basename($path),
            'source_year' => $sourceYear,
            'as_of_date' => $asOfDate,
            'dry_run' => $dryRun,
            'inward_index' => $indexReport,
            'total_read' => $stats['rows'],
            'planned_invoices' => count($invoices),
            'planned_products' => array_sum(array_map(fn ($i) => count($i['lines']), $invoices)),
            'planned_barcodes' => $stats['rows'],
            'resolution' => $stats['resolution'],
            'line_match' => $stats['line_match'],
            'anomalies' => $stats['anomalies'],
            'invoices' => 0,
            'products' => 0,
            'barcodes' => 0,
            'skipped_existing' => 0,
        ];

        if ($dryRun) {
            $summary['duration_seconds'] = round(microtime(true) - $startTime, 2);
            return $summary;
        }

        $this->write($invoices, $asOfDate, $shopId, $summary, $progress);
        $summary['duration_seconds'] = round(microtime(true) - $startTime, 2);

        return $summary;
    }

    // ---------------------------------------------------------------- collect

    /**
     * Stream the snapshot into challan-shaped buckets.
     *
     * @return array{0: array<string, array>, 1: array}
     */
    protected function collect(array $paths, ?callable $progress): array
    {
        // "do not clone duplicate": a barcode that appeared in an earlier year is
        // already loaded, and later sheets repeat it - by 2023-24 more than half of
        // each sheet is carried forward. First sighting wins, so the row keeps the
        // year and challan it actually arrived on.
        $seen = [];
        $invoices = [];
        $stats = [
            'rows' => 0,
            'resolution' => ['voucher+party' => 0, 'voucher' => 0, 'unresolved' => 0],
            'line_match' => [],
            'anomalies' => ['qty_not_one' => 0, 'missing_design' => 0, 'missing_party' => 0, 'blank_barcode' => 0],
            'per_snapshot' => [],
            'duplicate_rows' => 0,
        ];

        foreach ($paths as $snapshotYear => $path) {
        $before = $stats['rows'];
        $this->legacy->streamXlsxRowsAuto(
            $path,
            ['barcode', 'mrp'],
            function (array $cells, array $colMap, int $i) use (&$invoices, &$stats, &$seen, $progress) {
                $get = function (string $label) use ($cells, $colMap): string {
                    $col = $colMap[$label] ?? null;
                    return $col === null ? '' : trim((string)($cells[$col] ?? ''));
                };
                $num = fn (string $label): float => (float)str_replace(',', '', $get($label) ?: '0');
                // A column that is present and reads 0 is a fact - "no purchase
                // expense" - and must not be stored as NULL, which means "the
                // source does not carry this". Only absence yields NULL.
                $numOrNull = fn (string $label): ?float => isset($colMap[$label]) ? $num($label) : null;

                $barcode = $get('barcode');
                if ($barcode === '') {
                    $stats['anomalies']['blank_barcode']++;
                    return;
                }
                if (isset($seen[$barcode])) {
                    // Carried forward from a year already read; the row it produced
                    // then is the one that matters, so this sighting is skipped.
                    $stats['duplicate_rows']++;
                    return;
                }
                $seen[$barcode] = true;
                $stats['rows']++;

                $party = $get('party name');
                if ($party === '' || $party === '-') {
                    $party = 'Opening Stock Supplier';
                    $stats['anomalies']['missing_party']++;
                }

                $designNo = $get('design no.') ?: $get('design no');
                if ($designNo === '-') {
                    $designNo = '';
                }
                if ($designNo === '') {
                    $stats['anomalies']['missing_design']++;
                }

                // One sticker is one physical unit, so a qty<>1 row still yields a
                // single barcode. Counted so it can be checked on the shelf.
                if (abs($num('qty') - 1.0) > 0.001) {
                    $stats['anomalies']['qty_not_one']++;
                }

                $itemName = $get('item') ?: $get('product name');
                $hsn = $get('hsn code');
                $size = $get('size');
                $purRate = $num('pur. rate');

                // ---- place the barcode on its challan --------------------------
                $inwardDate = $this->legacy->parseLegacyDate($get('inward vch date'));
                $fy = $this->financialYearOf($inwardDate);
                $voucherNo = $get('inward vch no.') ?: $get('inward vch no');

                $hit = $this->inward->findVoucher($fy, $voucherNo, $party);
                $line = null;

                if ($hit !== null) {
                    $stats['resolution'][$hit['match']]++;
                    $key = $fy . '|' . ltrim($voucherNo, '0') . '|' . $hit['voucher']['party'];
                    $header = $hit['voucher'];

                    if ($this->inward->shapeOf($fy) === 'detail') {
                        $line = $this->inward->findLine($fy, $voucherNo, [
                            'item' => $itemName, 'size' => $size, 'hsn' => $hsn, 'rate' => $purRate,
                        ]);
                    }
                } else {
                    $stats['resolution']['unresolved']++;
                    // No challan to hang this on; bucket by party so the supplier
                    // attribution the snapshot does carry is not thrown away.
                    $key = 'snapshot|' . $party;
                    $header = null;
                }

                $matchLabel = $line['match'] ?? ($hit === null ? 'unresolved' : 'voucher-only');
                $stats['line_match'][$matchLabel] = ($stats['line_match'][$matchLabel] ?? 0) + 1;

                if (!isset($invoices[$key])) {
                    $invoices[$key] = [
                        'fy' => $fy,
                        'voucher_no' => $header['voucher_no'] ?? null,
                        'date' => $header['date'] ?? $inwardDate,
                        'party' => $header['party'] ?? $party,
                        'gstin' => $header['gstin'] ?? null,
                        'source' => $header === null
                            ? 'snapshot_only'
                            : ($this->inward->shapeOf($fy) === 'detail' ? 'inward_detail' : 'inward_summary'),
                        'resolved' => $header !== null,
                        'sgst' => $header['sgst'] ?? 0.0,
                        'cgst' => $header['cgst'] ?? 0.0,
                        'igst' => $header['igst'] ?? 0.0,
                        'net' => $header['net'] ?? 0.0,
                        'lines' => [],
                    ];
                }

                // One inward_products row per distinct SKU + price within the challan;
                // the barcodes under it become that row's quantity.
                $lineKey = md5(implode('|', [
                    $itemName, $designNo, $get('color'), $size, $num('mrp'), $purRate,
                ]));

                if (!isset($invoices[$key]['lines'][$lineKey])) {
                    $invoices[$key]['lines'][$lineKey] = [
                        'item_name' => $itemName,
                        'design_no' => $designNo,
                        'color' => $get('color'),
                        'size' => $size,
                        'hsn' => $hsn,
                        'mrp' => $num('mrp'),
                        'buy_price' => $purRate,
                        'net_rate' => $num('pur.net rate') ?: $purRate,
                        'purcost_rate' => $numOrNull('purcost. rate'),
                        'pur_exp_rate' => $numOrNull('pur exp. rate'),
                        'mark_up' => $num('mark up %'),
                        'mark_down' => $num('mark down %'),
                        // From the challan line, where one was found.
                        'tax_name' => $line['line']['tax_name'] ?? null,
                        'sales_rate' => $line['line']['sales_rate'] ?? null,
                        'sgst' => $line['line']['sgst'] ?? null,
                        'cgst' => $line['line']['cgst'] ?? null,
                        'igst' => $line['line']['igst'] ?? null,
                        'net_amount' => $line['line']['net'] ?? null,
                        'match' => $matchLabel,
                        'barcodes' => [],
                    ];
                }
                $invoices[$key]['lines'][$lineKey]['barcodes'][] = $barcode;

                if ($progress && $stats['rows'] % 5000 === 0) {
                    $progress('scan', $stats['rows']);
                }
            }
        );

        $stats['per_snapshot'][$snapshotYear] = $stats['rows'] - $before;
        }

        return [$invoices, $stats];
    }

    // ------------------------------------------------------------------ write

    protected function write(array $invoices, string $asOfDate, int $shopId, array &$summary, ?callable $progress): void
    {
        $existing = DB::table('product_barcodes')->pluck('barcode_number')->flip()->toArray();
        $hsnCache = DB::table('hsn_masters')->pluck('id', 'hsn_code')->toArray();
        $defaultHsnId = DB::table('hsn_masters')->value('id');
        $stamp = now()->format('Y-m-d H:i:s');
        $seq = 0;

        foreach ($invoices as $key => $invoice) {
            $seq++;

            // The legacy number repeats every year, so the stored voucher number
            // carries the year with it; the raw number stays in the challan field.
            $voucherNo = $invoice['resolved']
                ? 'OS-' . $invoice['fy'] . '-' . $invoice['voucher_no']
                : 'OS-SNAP-' . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);

            DB::transaction(function () use (
                $invoice, $voucherNo, $asOfDate, $shopId, $stamp,
                &$existing, $hsnCache, $defaultHsnId, &$summary
            ) {
                $partyAccId = $this->legacy->findOrCreateAccountMasterForParty($invoice['party'], $shopId);
                $inwardDate = $invoice['date'] ?: $asOfDate;

                $invoiceId = DB::table('inward_invoices')
                    ->where('shop_id', $shopId)
                    ->where('inward_voucher_no', $voucherNo)
                    ->value('id');

                if (!$invoiceId) {
                    $invoiceId = DB::table('inward_invoices')->insertGetId([
                        'shop_id' => $shopId,
                        'counter_master_id' => 1,
                        'vat_tax_id' => 2,
                        'inward_voucher_no' => $voucherNo,
                        'inward_challan_no' => $invoice['voucher_no'] ?: $voucherNo,
                        'inward_date' => $inwardDate,
                        'inward_day_name' => date('l', strtotime($inwardDate)),
                        'inward_time' => '00:00:00',
                        'inward_challan_date' => $inwardDate,
                        'inward_party_code' => $partyAccId,
                        'inward_total' => round($invoice['net'], 2),
                        'inward_acc_gst_amount' => round($invoice['sgst'] + $invoice['cgst'] + $invoice['igst'], 2),
                        'inward_acc_net_amount' => round($invoice['net'], 2),
                        'inward_acc_remark' => 'Opening Stock as at ' . $asOfDate,
                        'is_active' => 1,
                        'is_purchase' => 0,
                        'is_opening_stock' => 1,
                        'is_return' => 0,
                        'legacy_financial_year' => $invoice['fy'],
                        'legacy_party_gstin' => $invoice['gstin'] ?: null,
                        'legacy_source' => $invoice['source'],
                        'legacy_voucher_resolved' => $invoice['resolved'] ? 1 : 0,
                        'created_at' => $inwardDate . ' 00:00:00',
                        'updated_at' => $stamp,
                    ]);
                    $summary['invoices']++;
                }

                foreach ($invoice['lines'] as $line) {
                    $fresh = array_values(array_filter($line['barcodes'], fn ($b) => !isset($existing[$b])));
                    $summary['skipped_existing'] += count($line['barcodes']) - count($fresh);
                    if (empty($fresh)) {
                        continue;
                    }

                    $qty = count($fresh);
                    $productId = $this->legacy->resolveProductIdForBarcodeOrItem(
                        null, $line['item_name'], $line['design_no'], $shopId
                    );

                    $inwardProductId = DB::table('inward_products')->insertGetId([
                        'shop_id' => $shopId,
                        'inward_invoice_id' => $invoiceId,
                        'product_id' => $productId,
                        'design_master_id' => null,
                        'quantity' => $qty,
                        'buy_price' => $line['buy_price'],
                        'price' => $line['mrp'],
                        'discount_price' => 0.00,
                        'net_purc_price' => $line['net_rate'] * $qty,
                        'mrp' => $line['mrp'],
                        'mark_up' => $line['mark_up'],
                        'mark_down' => $line['mark_down'],
                        'net_purc_rate' => $line['net_rate'],
                        'hsn_master_id' => $hsnCache[$line['hsn']] ?? $defaultHsnId,
                        'vat_tax_id' => 2,
                        'tax_name' => $line['tax_name'],
                        'sgst_amount' => $line['sgst'],
                        'cgst_amount' => $line['cgst'],
                        'igst_amount' => $line['igst'],
                        'inward_sales_rate' => $line['sales_rate'],
                        'inward_net_amount' => $line['net_amount'],
                        'purcost_rate' => $line['purcost_rate'],
                        'pur_exp_rate' => $line['pur_exp_rate'],
                        'legacy_line_match' => $line['match'],
                        'is_active' => 1,
                        'is_online_product' => 0,
                        'created_at' => $inwardDate . ' 00:00:00',
                        'updated_at' => $stamp,
                    ]);
                    $summary['products']++;

                    foreach (array_chunk($fresh, 1000) as $chunk) {
                        $batch = [];
                        foreach ($chunk as $bc) {
                            $batch[] = [
                                'shop_id' => $shopId,
                                'product_id' => $productId,
                                'inward_invoice_id' => $invoiceId,
                                'inward_product_id' => $inwardProductId,
                                'barcode_number' => $bc,
                                'mrp' => $line['mrp'],
                                'is_printed' => 1,
                                'is_sold' => 0,
                                'created_at' => $inwardDate . ' 00:00:00',
                                'updated_at' => $stamp,
                            ];
                            $existing[$bc] = true;
                        }
                        DB::table('product_barcodes')->insert($batch);
                        $summary['barcodes'] += count($batch);
                    }
                }
            });

            if ($progress && $seq % 250 === 0) {
                $progress('write', $seq);
            }
        }
    }

    // ---------------------------------------------------------------- helpers

    protected function snapshotPath(string $year): ?string
    {
        $dir = rtrim($this->legacy->getBackupDir(), '/') . '/' . $year;
        if (!is_dir($dir)) {
            return null;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if (str_starts_with($entry, '.') || str_starts_with($entry, '~$')) {
                continue;
            }
            if (!preg_match('/\.xlsx?$/i', $entry)) {
                continue;
            }
            $stem = strtoupper(pathinfo($entry, PATHINFO_FILENAME));
            if (!str_contains($stem, 'BARCODE')) {
                continue;
            }
            // Kachi is out of scope; only the Paki snapshot counts.
            if (preg_match('/(^|[\s_\-])(K|KACHI|KACCHI|KACHHI)([\s_\-]|$)/i', $stem)) {
                continue;
            }

            return $dir . '/' . $entry;
        }

        return null;
    }

    /** Indian financial year: April to March. */
    protected function financialYearOf(?string $date): ?string
    {
        if ($date === null) {
            return null;
        }
        [$y, $m] = array_map('intval', explode('-', $date));

        return $m >= 4
            ? sprintf('%d-%d', $y, $y + 1)
            : sprintf('%d-%d', $y - 1, $y);
    }
}
