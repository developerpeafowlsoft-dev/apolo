<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;

/**
 * Reconciles the imported Opening Stock against a decade of legacy sale files.
 *
 * Two independent outputs from a single pass over "Voucher Detail SALE P.xlsx":
 *
 *  1. Sold-barcode detection. The Opening Stock snapshot is a list of barcodes the
 *     legacy system believed were on the shelf on 31-03-2026. Some of them also
 *     appear in the sale history. Most of those were sold and later returned, which
 *     is precisely why they are back in stock, so "appears in a sale file" is NOT
 *     the same as "sold". Each barcode's events are ordered by voucher date and the
 *     LAST one decides: a trailing Sale means the item left the shop, a trailing
 *     Return means it came back.
 *
 *     Netting quantities would be the obvious alternative and it is wrong here: the
 *     legacy export duplicates return rows (the same return booked twice against one
 *     original voucher), so the quantities do not sum to a meaningful figure.
 *
 *  2. Customer extraction. The legacy Account Master holds no debtors at all - every
 *     party ledger is a creditor - so retail customers exist only as an Account Name
 *     and Mobile1 on the sale rows themselves. Identity is the mobile number.
 *
 * File shapes differ across the decade (title block and header on row 3 until
 * 2019-20, header on row 1 and barcode one column left from 2020-21), so every
 * column is resolved by header name via streamXlsxRowsAuto() rather than by letter.
 */
class LegacySalesReconciliationService
{
    /** 2016-2017 ships no sale export at all, only the barcode snapshot. */
    protected const YEARS = [
        '2017-2018', '2018-2019', '2019-2020', '2020-2021', '2021-2022',
        '2022-2023', '2023-2024', '2024-2025', '2025-2026',
    ];

    /** Spatie role id for a retail customer. */
    protected const ROLE_CUSTOMER = 4;

    public function __construct(
        protected LegacyDataMigrationService $migration
    ) {
    }

    // ---------------------------------------------------------------- scanning

    /**
     * Single pass over every sale file.
     *
     * Only events for barcodes that are actually in stock are retained - holding
     * events for all 261k historically sold barcodes would cost memory for nothing.
     *
     * @return array{sold: array, returned: array, customers: array, stats: array, anomalies: array}
     */
    public function scan(int $shopId, ?callable $progress = null): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $stockBarcodes = DB::table('product_barcodes')
            ->where('shop_id', $shopId)
            ->pluck('id', 'barcode_number')
            ->toArray();

        $events = [];
        $customers = [];
        $stats = [
            'files' => 0, 'rows' => 0, 'data_rows' => 0, 'subtotal_rows' => 0,
            'sale_rows' => 0, 'return_rows' => 0, 'rows_without_mobile' => 0,
            'stock_barcodes' => count($stockBarcodes), 'per_year' => [],
        ];
        $seq = 0;

        foreach (self::YEARS as $year) {
            $path = $this->saleFilePath($year);
            if ($path === null) {
                $stats['per_year'][$year] = ['status' => 'missing'];
                continue;
            }

            $before = $stats['data_rows'];
            $stats['files']++;

            $this->migration->streamXlsxRowsAuto(
                $path,
                ['barcode', 'voucher no'],
                function (array $cells, array $colMap, int $i) use (
                    &$events, &$customers, &$stats, &$seq, $stockBarcodes, $year
                ) {
                    $stats['rows']++;

                    $get = function (string $label) use ($cells, $colMap): string {
                        $col = $colMap[$label] ?? null;
                        return $col === null ? '' : trim((string)($cells[$col] ?? ''));
                    };

                    // "Voucher No Total" / "Grand Total" subtotal bands are interleaved
                    // through the export and carry no barcode or party of their own.
                    $voucherNo = $get('voucher no');
                    if ($voucherNo !== '' && preg_match('/total/i', $voucherNo)) {
                        $stats['subtotal_rows']++;
                        return;
                    }
                    $barcode = $get('barcode');
                    if ($voucherNo === '' && $barcode === '') {
                        $stats['subtotal_rows']++;
                        return;
                    }

                    $stats['data_rows']++;
                    $seq++;

                    $qty = (float)($get('qty') ?: 1);
                    $isReturn = stripos($get('sales type'), 'return') !== false || $qty < 0;
                    $isReturn ? $stats['return_rows']++ : $stats['sale_rows']++;

                    $date = $this->parseLegacyDate($get('voucher date'));
                    $mobile = $this->normaliseMobile($get('mobile1'));
                    $name = $this->cleanName($get('account name'));

                    if ($mobile === '') {
                        $stats['rows_without_mobile']++;
                    } else {
                        if (!isset($customers[$mobile])) {
                            $customers[$mobile] = [
                                'mobile' => $mobile, 'name' => '', 'state' => '',
                                'names' => [], 'rows' => 0,
                                'first_seen' => null, 'last_seen' => null,
                            ];
                        }
                        $c = &$customers[$mobile];
                        $c['rows']++;
                        if ($name !== '') {
                            $c['names'][$name] = true;
                            // Latest spelling wins: people re-register with a corrected
                            // name far more often than the reverse.
                            if ($date === null || $c['last_seen'] === null || $date >= $c['last_seen']) {
                                $c['name'] = $name;
                            }
                        }
                        if ($c['state'] === '') {
                            $c['state'] = $get('state');
                        }
                        if ($date !== null) {
                            if ($c['first_seen'] === null || $date < $c['first_seen']) {
                                $c['first_seen'] = $date;
                            }
                            if ($c['last_seen'] === null || $date > $c['last_seen']) {
                                $c['last_seen'] = $date;
                            }
                        }
                        unset($c);
                    }

                    if ($barcode === '' || !isset($stockBarcodes[$barcode])) {
                        return;
                    }

                    $events[$barcode][] = [
                        // Undated rows sort last so they cannot be mistaken for the
                        // earliest event and silently flip a barcode's final state.
                        'date' => $date ?? '9999-12-31',
                        'seq' => $seq,
                        'year' => $year,
                        'voucher_no' => $voucherNo,
                        'is_return' => $isReturn,
                        'qty' => $qty,
                        'name' => $name,
                        'mobile' => $mobile,
                        'net' => (float)$get('net amt'),
                    ];
                }
            );

            $stats['per_year'][$year] = [
                'status' => 'ok',
                'data_rows' => $stats['data_rows'] - $before,
            ];

            if ($progress) {
                $progress($year, $stats['per_year'][$year]);
            }
        }

        [$sold, $returned, $anomalies] = $this->classify($events, $stockBarcodes);

        foreach ($customers as $mobile => $c) {
            if (strlen($mobile) !== 10) {
                $anomalies[] = [
                    'type' => 'odd_mobile',
                    'mobile' => $mobile,
                    'name' => $c['name'],
                    'detail' => strlen($mobile) . ' digits, expected 10',
                ];
            }
            if (count($c['names']) > 1) {
                $anomalies[] = [
                    'type' => 'name_conflict',
                    'mobile' => $mobile,
                    'name' => $c['name'],
                    'detail' => 'also seen as: ' . implode(' / ', array_slice(
                        array_diff(array_keys($c['names']), [$c['name']]), 0, 4
                    )),
                ];
            }
        }

        return [
            'sold' => $sold,
            'returned' => $returned,
            'customers' => $customers,
            'stats' => $stats,
            'anomalies' => $anomalies,
        ];
    }

    /**
     * Decide each in-stock barcode's final state from its event history.
     *
     * Ordering is by voucher date with the file sequence as tie-break, because rows
     * sharing a date (a same-day return and resale) are only separable by position.
     */
    protected function classify(array $events, array $stockBarcodes): array
    {
        $sold = [];
        $returned = [];
        $anomalies = [];

        foreach ($events as $barcode => $rows) {
            usort($rows, fn ($a, $b) => $a['date'] === $b['date']
                ? $a['seq'] <=> $b['seq']
                : strcmp($a['date'], $b['date']));

            $last = end($rows);
            $record = [
                'barcode' => $barcode,
                'barcode_id' => $stockBarcodes[$barcode],
                'events' => count($rows),
                'last_date' => $last['date'] === '9999-12-31' ? null : $last['date'],
                'last_year' => $last['year'],
                'last_voucher_no' => $last['voucher_no'],
                'customer_name' => $last['name'],
                'customer_mobile' => $last['mobile'],
                'net' => $last['net'],
            ];

            if ($last['is_return']) {
                $returned[$barcode] = $record;
                continue;
            }

            $sold[$barcode] = $record;

            // The snapshot says on the shelf, the history says sold. Someone should
            // look at these even though we act on the history.
            $anomalies[] = [
                'type' => 'sold_but_in_stock',
                'mobile' => $last['mobile'],
                'name' => $last['name'],
                'detail' => sprintf(
                    '%s: last event %s vch %s (%s), %d event(s) total',
                    $barcode, $record['last_date'] ?? 'undated',
                    $last['voucher_no'], $last['year'], count($rows)
                ),
            ];
        }

        return [$sold, $returned, $anomalies];
    }

    // ---------------------------------------------------------------- applying

    /**
     * Flag the resolved barcodes as sold.
     *
     * Scoped to the shop: barcode_number carries a global unique index, so matching
     * on the number alone can land on a row owned by a different shop and retire its
     * stock on the strength of this shop's sale file.
     */
    public function applySoldFlags(array $sold, int $shopId, bool $dryRun): array
    {
        $ids = array_column($sold, 'barcode_id');
        if (empty($ids)) {
            return ['candidates' => 0, 'already_sold' => 0, 'updated' => 0];
        }

        // Chunked like the update below. MySQL caps a prepared statement at 65,535
        // placeholders, and once opening stock carries the whole decade the sold set
        // runs well past that - an unbounded whereIn here aborted the run before a
        // single flag was written.
        $alreadySold = 0;
        foreach (array_chunk($ids, 2000) as $chunk) {
            $alreadySold += DB::table('product_barcodes')
                ->where('shop_id', $shopId)
                ->whereIn('id', $chunk)
                ->where('is_sold', 1)
                ->count();
        }

        if ($dryRun) {
            return [
                'candidates' => count($ids),
                'already_sold' => $alreadySold,
                'updated' => count($ids) - $alreadySold,
                'dry_run' => true,
            ];
        }

        $updated = 0;
        foreach (array_chunk($ids, 500) as $chunk) {
            $updated += DB::table('product_barcodes')
                ->where('shop_id', $shopId)
                ->whereIn('id', $chunk)
                ->where('is_sold', 0)
                ->update(['is_sold' => 1, 'updated_at' => now()]);
        }

        return [
            'candidates' => count($ids),
            'already_sold' => $alreadySold,
            'updated' => $updated,
        ];
    }

    /**
     * Create a user + customer for every distinct legacy mobile that has none.
     *
     * Deduplicated on users.phone, soft-deleted rows included, so a re-run is a
     * no-op rather than a second copy of 16k people.
     *
     * Passwords are left NULL on purpose. The obvious route - the POS checkout's
     * UserRepository::registerNewUser() - calls Hash::make($request->password),
     * and with no password supplied that stores a valid bcrypt hash of the empty
     * string on every account, which any login path comparing against '' would
     * accept. These are migrated records, not registrations; they get credentials
     * when the customer actually signs up.
     */
    public function importCustomers(array $customers, bool $dryRun): array
    {
        $result = [
            'legacy_total' => count($customers),
            'existing' => 0, 'created' => 0, 'linked' => 0, 'skipped' => 0,
        ];
        if (empty($customers)) {
            return $result;
        }

        $existing = [];
        foreach (array_chunk(array_keys($customers), 2000) as $chunk) {
            DB::table('users')
                ->whereIn('phone', $chunk)
                ->select('id', 'phone')
                ->get()
                ->each(function ($u) use (&$existing) {
                    $existing[(string)$u->phone] = $u->id;
                });
        }
        $result['existing'] = count($existing);

        $toCreate = array_diff_key($customers, $existing);
        $result['created'] = count($toCreate);

        if ($dryRun) {
            $result['linked'] = count($toCreate);
            $result['dry_run'] = true;
            return $result;
        }

        $now = now();
        $createdIds = [];

        foreach (array_chunk($toCreate, 500, true) as $chunk) {
            $rows = [];
            foreach ($chunk as $mobile => $c) {
                [$first, $last] = $this->splitName($c['name']);
                $rows[] = [
                    'name' => $first,
                    'last_name' => $last,
                    'phone' => $mobile,
                    'email' => null,
                    'password' => null,
                    'is_active' => 1,
                    'created_at' => $c['first_seen'] ? $c['first_seen'] . ' 00:00:00' : $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('users')->insert($rows);

            DB::table('users')
                ->whereIn('phone', array_keys($chunk))
                ->select('id', 'phone')
                ->get()
                ->each(function ($u) use (&$createdIds) {
                    $createdIds[(string)$u->phone] = $u->id;
                });
        }

        $userIds = array_values(array_intersect_key($createdIds, $toCreate));

        foreach (array_chunk($userIds, 500) as $chunk) {
            DB::table('model_has_roles')->insertOrIgnore(array_map(fn ($id) => [
                'role_id' => self::ROLE_CUSTOMER,
                'model_type' => 'App\\Models\\User',
                'model_id' => $id,
            ], $chunk));
        }

        // Pre-existing users are linked too, but only if they already hold the
        // customer role. A staff or admin account whose phone happens to match a
        // legacy mobile must not be turned into a customer as a side effect.
        // Chunked for the same reason as the barcode queries: this list is as long as
        // the number of legacy mobiles that already have a user, and an unbounded
        // whereIn hits MySQL's 65,535-placeholder ceiling.
        $existingCustomerIds = [];
        foreach (array_chunk(array_values($existing), 2000) as $chunk) {
            $existingCustomerIds = array_merge($existingCustomerIds, DB::table('model_has_roles')
                ->where('role_id', self::ROLE_CUSTOMER)
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $chunk)
                ->pluck('model_id')
                ->all());
        }

        $allUserIds = array_values(array_unique(array_merge($userIds, $existingCustomerIds)));
        $linkedAlready = [];
        foreach (array_chunk($allUserIds, 2000) as $chunk) {
            DB::table('customers')->whereIn('user_id', $chunk)->pluck('user_id')
                ->each(function ($id) use (&$linkedAlready) {
                    $linkedAlready[$id] = true;
                });
        }

        $needLink = array_values(array_filter($allUserIds, fn ($id) => !isset($linkedAlready[$id])));
        foreach (array_chunk($needLink, 500) as $chunk) {
            DB::table('customers')->insert(array_map(fn ($id) => [
                'user_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }

        $result['linked'] = count($needLink);

        return $result;
    }

    // ----------------------------------------------------------------- helpers

    /**
     * Locate the year's detail-level sale export.
     *
     * Deliberately not routed through resolveFilePath(): several years ship both a
     * "Voucher Detail ..." and a "Voucher Wise ..." sale export, findYearFile()
     * picks between them alphabetically, and only the detail one carries a barcode
     * per line. Depending on "Detail" sorting before "Wise" would work today and
     * break the day someone renames an export.
     */
    protected function saleFilePath(string $year): ?string
    {
        $dir = rtrim($this->migration->getBackupDir(), "/") . "/" . $year;
        if (!is_dir($dir)) {
            return null;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if (str_starts_with($entry, ".") || str_starts_with($entry, "~$")) {
                continue;
            }
            if (!preg_match("/\.xlsx?$/i", $entry)) {
                continue;
            }
            $stem = strtoupper(pathinfo($entry, PATHINFO_FILENAME));
            // Kachi (unofficial) books are out of scope; only the Paki export counts.
            if (preg_match("/(^|[\s_\-])(K|KACHI|KACCHI|KACHHI)([\s_\-]|$)/i", $stem)) {
                continue;
            }
            if (str_contains($stem, "DETAIL") && str_contains($stem, "SALE")) {
                return $dir . "/" . $entry;
            }
        }

        return null;
    }

    /**
     * Legacy dates arrive as an Excel serial (pre-2020 exports) or dd/mm/yyyy.
     */
    protected function parseLegacyDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float)$value;
            // Anything outside ~1954..2064 is a stray number, not a date.
            if ($serial > 20000 && $serial < 60000) {
                return date('Y-m-d', (int)round(($serial - 25569) * 86400));
            }
            return null;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})#', $value, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        return null;
    }

    /**
     * Reduce a legacy Mobile1 to bare digits, dropping an Indian country code or a
     * trunk prefix so "+91 98250 47770", "09825047770" and "9825047770" are one
     * customer rather than three.
     */
    protected function normaliseMobile(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return '';
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        // All-zero or obviously placeholder numbers are not identities.
        return preg_match('/^0+$/', $digits) ? '' : $digits;
    }

    protected function cleanName(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        return strcasecmp($value, 'cash customer') === 0 ? '' : $value;
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    protected function splitName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['Walk-in', 'Customer'];
        }
        $parts = explode(' ', $name, 2);
        return [$parts[0], $parts[1] ?? null];
    }
}
