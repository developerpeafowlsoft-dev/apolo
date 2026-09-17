<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Step 9 tagged opening balances with "the active financial year", which -
 * because every year was flagged active - resolved to the earliest row, 2016-17.
 * The flag is fixed (2026_09_15_174500); this moves the 65 balances it misfiled
 * onto the year they actually open.
 *
 * Reversal moves them back, excluding balances that have voucher entries behind
 * them: those are voucher-derived and legitimately belong to the current year.
 */
return new class extends Migration
{
    private const SHOP_ID = 14;
    private const FROM_FY = '2016-2017';
    private const TO_FY   = '2026-2027';

    public function up(): void
    {
        $this->move(self::FROM_FY, self::TO_FY, false);
    }

    public function down(): void
    {
        $this->move(self::TO_FY, self::FROM_FY, true);
    }

    private function move(string $fromName, string $toName, bool $excludeVoucherBacked): void
    {
        $from = DB::table('financial_years')->where('name', $fromName)->value('id');
        $to   = DB::table('financial_years')->where('name', $toName)->value('id');

        if (!$from || !$to) {
            echo "  financial year {$fromName} or {$toName} not found - nothing moved\n";
            return;
        }

        $query = DB::table('account_balances')
            ->where('shop_id', self::SHOP_ID)
            ->where('financial_year_id', $from);

        if ($excludeVoucherBacked) {
            $query->whereNotIn('account_id', DB::table('voucher_entries')->distinct()->select('account_id'));
        }

        $movingIds = $query->pluck('account_id');

        if ($movingIds->isEmpty()) {
            echo "  no balances in {$fromName} for shop " . self::SHOP_ID . " - nothing to move\n";
            return;
        }

        // unique_balance_entry is (account_id, financial_year_id, shop_id): a row
        // already sitting at the target would make this fail mid-update.
        $clash = DB::table('account_balances')
            ->where('shop_id', self::SHOP_ID)
            ->where('financial_year_id', $to)
            ->whereIn('account_id', $movingIds)
            ->pluck('account_id');

        if ($clash->isNotEmpty()) {
            echo "  ABORTED - {$clash->count()} account(s) already hold a {$toName} balance: " . $clash->implode(', ') . "\n";
            return;
        }

        $moved = DB::table('account_balances')
            ->where('shop_id', self::SHOP_ID)
            ->where('financial_year_id', $from)
            ->whereIn('account_id', $movingIds)
            ->update(['financial_year_id' => $to, 'updated_at' => now()]);

        echo "  re-tagged {$moved} opening balance(s): {$fromName} -> {$toName}\n";
    }
};
