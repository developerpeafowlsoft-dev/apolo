<?php

namespace App\Services\OpeningStock;

use App\Services\Migration\LegacyDataMigrationService;

/**
 * Reads every year's inward challan export into a lookup the opening-stock
 * importer can join against.
 *
 * Legacy voucher numbers restart at 00001 each April, so a voucher is only
 * identified by (financial year, voucher no) - the year coming from the barcode
 * snapshot's "Inward Vch Date". Party name is carried as a second key because
 * a handful of vouchers in a year share a number across day books.
 *
 * Two export shapes exist and both are handled:
 *
 *   detail   one row per item  - Voucher No, Date, Account Name, Mobile1, GST No,
 *                                HSN Code, Item Name, Size1, Qty, Purc Rate,
 *                                Sales Rate, Tax Name, SGST/CGST/IGST, Net Amount
 *   summary  one row per bill  - the same financials without HSN, Item or Size
 *
 * FY 2016-17 ships no inward export at all; callers get null for it and fall
 * back to the snapshot alone.
 */
class LegacyInwardIndex
{
    /** @var array<string, array<string, array>> fy => voucherKey => header */
    protected array $vouchers = [];

    /** @var array<string, array<string, array<int, array>>> fy => voucherKey => lines */
    protected array $lines = [];

    /** @var array<string, string> fy => detail|summary */
    protected array $shapes = [];

    /** @var array<string, string> file md5 => the first fy that claimed it */
    protected array $seenFiles = [];

    public function __construct(
        protected LegacyDataMigrationService $legacy
    ) {
    }

    /**
     * @param  string[]  $years
     * @return array<string, array{shape: string, vouchers: int, lines: int}>
     */
    public function build(array $years, ?callable $progress = null): array
    {
        $report = [];

        foreach ($years as $fy) {
            $path = $this->inwardFilePath($fy);
            if ($path === null) {
                $report[$fy] = ['shape' => 'none', 'vouchers' => 0, 'lines' => 0];
                continue;
            }

            // A year whose export is byte-identical to one already read is not a
            // second year of data - it is the same file delivered twice. The
            // 2024-2025 folder is a verbatim copy of 2020-2021, and reading it
            // would attach 2020-21 suppliers, rates and GST to 2024-25 stock:
            // 3,526 barcodes would silently receive figures from the wrong year.
            // Detected by content rather than by hardcoding the year, so a repeat
            // of the mistake in a future export is caught the same way.
            $hash = md5_file($path);
            if ($hash !== false && isset($this->seenFiles[$hash])) {
                $report[$fy] = [
                    'shape' => 'duplicate of ' . $this->seenFiles[$hash],
                    'vouchers' => 0,
                    'lines' => 0,
                ];
                if ($progress) {
                    $progress($fy, $report[$fy]);
                }
                continue;
            }
            if ($hash !== false) {
                $this->seenFiles[$hash] = $fy;
            }

            $shape = str_contains(strtoupper(basename($path)), 'DETAIL') ? 'detail' : 'summary';
            $this->shapes[$fy] = $shape;
            $lineCount = 0;

            $this->legacy->streamXlsxRowsAuto(
                $path,
                ['voucher no', 'account name'],
                function (array $cells, array $colMap, int $i) use ($fy, $shape, &$lineCount) {
                    $get = function (string $label) use ($cells, $colMap): string {
                        $col = $colMap[$label] ?? null;
                        return $col === null ? '' : trim((string)($cells[$col] ?? ''));
                    };
                    $num = fn (string $label): float => (float)str_replace(',', '', $get($label) ?: '0');

                    $voucherNo = $get('voucher no');
                    if ($voucherNo === '' || preg_match('/total/i', $voucherNo)) {
                        return;
                    }

                    $key = $this->voucherKey($voucherNo);
                    $party = $get('account name');

                    if (!isset($this->vouchers[$fy][$key])) {
                        $this->vouchers[$fy][$key] = [
                            'voucher_no' => $voucherNo,
                            'date' => $this->legacy->parseLegacyDate($get('voucher date')),
                            'party' => $party,
                            'party_norm' => $this->normaliseParty($party),
                            'mobile' => $get('mobile1'),
                            'gstin' => $get('gst no'),
                            'shape' => $shape,
                            'qty' => 0.0,
                            'sgst' => 0.0,
                            'cgst' => 0.0,
                            'igst' => 0.0,
                            'net' => 0.0,
                        ];
                    }

                    $v = &$this->vouchers[$fy][$key];
                    $v['qty'] += $num('qty');
                    $v['sgst'] += $num('sgst amt');
                    $v['cgst'] += $num('cgstamt') ?: $num('cgst amt');
                    $v['igst'] += $num('igst amt');
                    $v['net'] += $num('net amount');
                    if ($v['party'] === '' && $party !== '') {
                        $v['party'] = $party;
                        $v['party_norm'] = $this->normaliseParty($party);
                    }
                    unset($v);

                    // Only the detail shape carries per-item rows worth indexing.
                    if ($shape !== 'detail') {
                        return;
                    }

                    $this->lines[$fy][$key][] = [
                        'item' => $this->normaliseText($get('item name')),
                        'item_raw' => $get('item name'),
                        'size' => $this->normaliseText($get('size1')),
                        'hsn' => preg_replace('/\D/', '', $get('hsn code')) ?: '',
                        'rate' => $this->money($num('purc rate')),
                        'sales_rate' => $num('sales rate'),
                        'tax_name' => $get('tax name'),
                        'sgst' => $num('sgst amt'),
                        'cgst' => $num('cgstamt') ?: $num('cgst amt'),
                        'igst' => $num('igst amt'),
                        'net' => $num('net amount'),
                        'qty' => $num('qty'),
                    ];
                    $lineCount++;
                }
            );

            $report[$fy] = [
                'shape' => $shape,
                'vouchers' => count($this->vouchers[$fy] ?? []),
                'lines' => $lineCount,
            ];

            if ($progress) {
                $progress($fy, $report[$fy]);
            }
        }

        return $report;
    }

    /**
     * Resolve a barcode's inward voucher. Party is advisory: a voucher-number hit
     * within the right year is accepted even when the party spelling differs,
     * because the snapshot and the challan disagree on punctuation often enough
     * that requiring both would drop real matches.
     *
     * @return array{voucher: array, match: string}|null
     */
    public function findVoucher(?string $fy, string $voucherNo, string $party): ?array
    {
        if ($fy === null || !isset($this->vouchers[$fy])) {
            return null;
        }

        $key = $this->voucherKey($voucherNo);
        $voucher = $this->vouchers[$fy][$key] ?? null;
        if ($voucher === null) {
            return null;
        }

        return [
            'voucher' => $voucher,
            'match' => $voucher['party_norm'] === $this->normaliseParty($party) ? 'voucher+party' : 'voucher',
        ];
    }

    /**
     * Place a barcode on one of its voucher's item lines.
     *
     * Item name, size and purchase rate together identify the line in 99.5% of
     * cases; the looser attempts below recover most of the rest. The label is
     * returned so the stored row records how firm the match was.
     *
     * @return array{line: array, match: string}|null
     */
    public function findLine(string $fy, string $voucherNo, array $probe): ?array
    {
        $candidates = $this->lines[$fy][$this->voucherKey($voucherNo)] ?? [];
        if (empty($candidates)) {
            return null;
        }

        $item = $this->normaliseText($probe['item'] ?? '');
        $size = $this->normaliseText($probe['size'] ?? '');
        $hsn = preg_replace('/\D/', '', (string)($probe['hsn'] ?? '')) ?: '';
        $rate = $this->money((float)($probe['rate'] ?? 0));

        $attempts = [
            'item+size+rate' => fn ($c) => $c['item'] === $item && $c['size'] === $size && $c['rate'] === $rate,
            'item+rate' => fn ($c) => $c['item'] === $item && $c['rate'] === $rate,
            'hsn+size+rate' => fn ($c) => $c['hsn'] !== '' && $c['hsn'] === $hsn && $c['size'] === $size && $c['rate'] === $rate,
            'item+size' => fn ($c) => $c['item'] === $item && $c['size'] === $size,
            'rate' => fn ($c) => $c['rate'] === $rate,
        ];

        foreach ($attempts as $label => $test) {
            foreach ($candidates as $candidate) {
                if ($test($candidate)) {
                    return ['line' => $candidate, 'match' => $label];
                }
            }
        }

        return null;
    }

    public function shapeOf(string $fy): ?string
    {
        return $this->shapes[$fy] ?? null;
    }

    // ----------------------------------------------------------------- helpers

    /**
     * Locate the year's inward export. Excel lock files (~$) and AppleDouble
     * metadata (._) live alongside the real exports and must not be picked up -
     * 2019-2020 ships a lock file whose name sorts first.
     */
    protected function inwardFilePath(string $fy): ?string
    {
        $dir = rtrim($this->legacy->getBackupDir(), '/') . '/' . $fy;
        if (!is_dir($dir)) {
            return null;
        }

        $detail = null;
        $summary = null;

        foreach (scandir($dir) ?: [] as $entry) {
            if (str_starts_with($entry, '.') || str_starts_with($entry, '~$')) {
                continue;
            }
            if (!preg_match('/\.xlsx?$/i', $entry)) {
                continue;
            }
            $stem = strtoupper(pathinfo($entry, PATHINFO_FILENAME));
            if (preg_match('/(^|[\s_\-])(K|KACHI|KACCHI|KACHHI)([\s_\-]|$)/i', $stem)) {
                continue;
            }
            // INWART is a real typo in the 2018-19 export.
            if (!preg_match('/INWARD|INWART|INCHALLAN/', $stem)) {
                continue;
            }
            str_contains($stem, 'DETAIL') ? $detail ??= $entry : $summary ??= $entry;
        }

        $chosen = $detail ?? $summary;

        return $chosen === null ? null : $dir . '/' . $chosen;
    }

    protected function voucherKey(string $voucherNo): string
    {
        return ltrim(trim($voucherNo), '0') ?: '0';
    }

    protected function normaliseParty(string $name): string
    {
        return $this->normaliseText($name);
    }

    protected function normaliseText(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9 ]/', '', $value) ?? '';

        return preg_replace('/\s+/', ' ', strtoupper(trim($value))) ?? '';
    }

    /** Rates are compared as fixed-point strings; floats do not compare reliably. */
    protected function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
