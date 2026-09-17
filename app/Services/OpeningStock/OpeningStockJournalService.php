<?php

namespace App\Services\OpeningStock;

use App\Services\Migration\LegacyDataMigrationService;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Posts the one-time opening journal for imported Opening Stock.
 *
 *   Dr  Opening Stock            (asset, Stock-in-Hand)
 *       Cr  Opening Balance Equity   (equity, Capital Account)
 *
 * Opening stock is NOT a purchase - no supplier is credited and no GST is
 * claimed, because the tax on these goods was accounted for years ago in the
 * legacy system. The contra is equity, which is what brings the imported stock
 * onto the balance sheet without inventing a payable.
 */
class OpeningStockJournalService
{
    protected const STOCK_GROUP = 6;    // Stock-in-Hand / Inventory (Assets)
    protected const CAPITAL_GROUP = 13; // Capital Account (Equity)

    protected LegacyDataMigrationService $legacy;

    public function __construct(LegacyDataMigrationService $legacy)
    {
        $this->legacy = $legacy;
    }

    /**
     * @param  string  $asOfDate  Journal date - normally the first day of the FY the stock opens into
     * @param  string  $basis     'net' (cost after trade discount) or 'gross' (list purchase rate)
     */
    public function post(
        string $asOfDate = '2026-04-01',
        string $basis = 'net',
        bool $dryRun = true,
        int $shopId = 14,
        string $suffix = ''
    ): array {
        if (!in_array($basis, ['net', 'gross'], true)) {
            throw new Exception("Unknown valuation basis '{$basis}'. Use 'net' or 'gross'.");
        }

        // Valued per UNSOLD barcode, not per line.
        //
        // Opening stock now holds every barcode the shop has ever owned - 166,769 of
        // them - because the whole decade was loaded so the sale files could mark what
        // left. Summing every line would put roughly Rs 3.95 crore of inventory on the
        // balance sheet, most of it garments sold years ago. What the journal must
        // state is the stock actually on the shelf, which is the barcodes still
        // flagged unsold: Rs 1.52 crore across 47,926 of them.
        //
        // Per barcode rather than per line, because a line's barcodes can now be
        // partly sold - three received, one gone - so the line quantity no longer
        // describes what is held.
        $rows = DB::table('product_barcodes as pb')
            ->join('inward_products as ip', 'ip.id', '=', 'pb.inward_product_id')
            ->join('inward_invoices as i', 'ip.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shopId)
            ->where('i.is_opening_stock', 1)
            ->where('pb.is_sold', 0);

        $units = (float) (clone $rows)->count();
        if ($units <= 0) {
            throw new Exception("No unsold opening stock found for shop {$shopId}. Run opening-stock:import first.");
        }

        $amount = $basis === 'net'
            ? (float) (clone $rows)->sum('ip.net_purc_rate')
            : (float) (clone $rows)->sum('ip.buy_price');

        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new Exception('Opening stock valuation came out as zero - refusing to post an empty journal.');
        }

        $fyId = $this->legacy->getFinancialYearIdForDate($asOfDate);
        if (!$fyId) {
            throw new Exception("No financial year covers {$asOfDate}.");
        }

        $voucherNo = 'JV-OS-' . str_replace('-', '', $asOfDate) . $suffix;

        $existing = DB::table('vouchers')
            ->where('shop_id', $shopId)
            ->where('voucher_no', $voucherNo)
            ->value('id');

        $result = [
            'voucher_no'       => $voucherNo,
            'date'             => $asOfDate,
            'financial_year_id'=> $fyId,
            'basis'            => $basis,
            'units'            => $units,
            'amount'           => $amount,
            'dry_run'          => $dryRun,
            'already_posted'   => (bool) $existing,
            'regrouped_account'=> null,
            'created_accounts' => [],
        ];

        if ($existing) {
            $result['voucher_id'] = (int) $existing;
            return $result;
        }

        if ($dryRun) {
            $result['debit_account']  = $this->describeAccount($this->findStockAccount(), 'OPENING STOCK', self::STOCK_GROUP);
            $result['credit_account'] = $this->describeAccount($this->findEquityAccount(), 'Opening Balance Equity', self::CAPITAL_GROUP);
            return $result;
        }

        DB::transaction(function () use ($voucherNo, $asOfDate, $fyId, $shopId, $amount, $basis, &$result) {
            $drId = $this->ensureStockAccount($result);
            $crId = $this->ensureEquityAccount($result);

            $voucherId = DB::table('vouchers')->insertGetId([
                'voucher_no'        => $voucherNo,
                'voucher_type'      => 'journal',
                'status'            => 'posted',
                'date'              => $asOfDate,
                'narration'         => 'Opening Stock brought forward as at ' . $asOfDate
                                       . ' (' . ($basis === 'net' ? 'net purchase cost' : 'gross purchase rate') . ')',
                'shop_id'           => $shopId,
                'financial_year_id' => $fyId,
                'sequence_id'       => $this->legacy->getOrCreateVoucherSequenceId('Journal', $fyId, $shopId),
                'created_at'        => $asOfDate . ' 00:00:00',
                'updated_at'        => now(),
            ]);

            $stamp = now();
            DB::table('voucher_entries')->insert([
                [
                    'voucher_id'  => $voucherId,
                    'account_id'  => $drId,
                    'type'        => 'Dr',
                    'amount'      => $amount,
                    'description' => 'Opening stock brought forward',
                    'created_at'  => $stamp,
                    'updated_at'  => $stamp,
                ],
                [
                    'voucher_id'  => $voucherId,
                    'account_id'  => $crId,
                    'type'        => 'Cr',
                    'amount'      => $amount,
                    'description' => 'Opening balance equity',
                    'created_at'  => $stamp,
                    'updated_at'  => $stamp,
                ],
            ]);

            // Never leave an unbalanced voucher behind.
            $dr = (float) DB::table('voucher_entries')->where('voucher_id', $voucherId)->where('type', 'Dr')->sum('amount');
            $cr = (float) DB::table('voucher_entries')->where('voucher_id', $voucherId)->where('type', 'Cr')->sum('amount');
            if (round($dr - $cr, 2) !== 0.0) {
                throw new Exception("Journal did not balance: Dr {$dr} vs Cr {$cr}. Rolled back.");
            }

            $result['voucher_id'] = $voucherId;
            $result['debit_account_id'] = $drId;
            $result['credit_account_id'] = $crId;
        });

        return $result;
    }

    protected function findStockAccount(): ?object
    {
        return DB::table('accounts')
            ->where('code', 'OPENINGSTO_113')
            ->orWhere('code', 'OPEN_STOCK')
            ->first();
    }

    protected function findEquityAccount(): ?object
    {
        return DB::table('accounts')->where('code', 'OPEN_BAL_EQ')->first();
    }

    protected function describeAccount(?object $acc, string $fallbackName, int $group): string
    {
        if (!$acc) {
            return "{$fallbackName} (will be created in group {$group})";
        }
        $note = $acc->account_group_id != $group ? " [will move from group {$acc->account_group_id} to {$group}]" : '';

        return "{$acc->name} ({$acc->code}){$note}";
    }

    protected function ensureStockAccount(array &$result): int
    {
        $acc = $this->findStockAccount();

        if (!$acc) {
            $id = DB::table('accounts')->insertGetId([
                'name'             => 'OPENING STOCK',
                'code'             => 'OPEN_STOCK',
                'account_group_id' => self::STOCK_GROUP,
                'remark'           => 'Opening stock brought forward from legacy ERP',
                'is_active'        => 1,
                'is_default'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $result['created_accounts'][] = 'OPENING STOCK (OPEN_STOCK)';

            return $id;
        }

        // The legacy import filed stock accounts under Sundry Creditors, which
        // would show inventory as a liability. Correct it before posting.
        if ($acc->account_group_id != self::STOCK_GROUP) {
            DB::table('accounts')->where('id', $acc->id)->update([
                'account_group_id' => self::STOCK_GROUP,
                'updated_at'       => now(),
            ]);
            $result['regrouped_account'] = "{$acc->name} ({$acc->code}): group {$acc->account_group_id} -> " . self::STOCK_GROUP;
        }

        return (int) $acc->id;
    }

    protected function ensureEquityAccount(array &$result): int
    {
        $acc = $this->findEquityAccount();
        if ($acc) {
            return (int) $acc->id;
        }

        $id = DB::table('accounts')->insertGetId([
            'name'             => 'Opening Balance Equity',
            'code'             => 'OPEN_BAL_EQ',
            'account_group_id' => self::CAPITAL_GROUP,
            'remark'           => 'Contra for opening balances brought forward from legacy ERP',
            'is_active'        => 1,
            'is_default'       => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $result['created_accounts'][] = 'Opening Balance Equity (OPEN_BAL_EQ)';

        return $id;
    }
}
