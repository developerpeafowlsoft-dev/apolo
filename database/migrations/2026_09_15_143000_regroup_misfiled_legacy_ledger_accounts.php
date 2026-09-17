<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrects 14 ledger accounts the legacy import filed under a group whose
 * account TYPE contradicts what the account records - inventory booked as a
 * liability, expenses booked as income, trading results booked as payables.
 *
 * Run while the ledger is effectively empty (2 voucher entries, neither against
 * these accounts), so nothing that has been posted against is disturbed. Each
 * row is matched on id AND name AND its expected current group, so the migration
 * is a no-op anywhere the data does not look exactly as audited.
 *
 * Audit: 15 Sep 2026, shop 14, apolo_db_new.
 */
return new class extends Migration
{
    /** [id, name, from_group, to_group] */
    private array $moves = [
        // Inventory filed as a liability -> Stock-in-Hand / Inventory (Assets)
        [131, 'CLOSING STOCK',            10, 6],
        [132, 'CLOSING STOCK (B/S)',      10, 6],
        [133, 'CLOSING STOCK (P&L)',      10, 6],
        [356, 'OPENING STOCK A/C.',       10, 6],

        // Trading results filed as payables -> Reserves & Surplus (Equity)
        [197, 'GROSS PROFIT',             10, 14],
        [341, 'NET PROFIT A/C',           10, 14],
        [386, 'PROFIT & LOSS A/C',        10, 14],
        [387, 'PROFIT & LOSS A/C.',       10, 14],

        // Purchase ledger scattered -> Purchase Accounts (Expenses)
        [389, 'PURCHASE A/C',             10, 19],
        [390, 'PURCHASE A/C.',            21, 19],
        [392, 'PURCHASE RETURNED',        21, 19],

        // Expenses filed as income -> Indirect Expenses
        [425, 'SALARY A/C.',              18, 21],
        [480, 'TEA EXP. A/C.',            18, 21],
        [113, 'BANK CHARGE',              18, 21],
    ];

    public function up(): void
    {
        $this->apply(fn ($m) => [$m[2], $m[3]]);
    }

    public function down(): void
    {
        $this->apply(fn ($m) => [$m[3], $m[2]]);
    }

    private function apply(callable $direction): void
    {
        $applied = 0;
        $skipped = [];

        foreach ($this->moves as $move) {
            [$from, $to] = $direction($move);
            [$id, $name] = $move;

            $affected = DB::table('accounts')
                ->where('id', $id)
                ->where('name', $name)
                ->where('account_group_id', $from)
                ->update([
                    'account_group_id' => $to,
                    'updated_at'       => now(),
                ]);

            $affected ? $applied++ : $skipped[] = "{$id} {$name}";
        }

        echo "  regrouped {$applied} of " . count($this->moves) . " accounts\n";
        if ($skipped) {
            echo "  skipped (did not match audited state): " . implode('; ', $skipped) . "\n";
        }
    }
};
