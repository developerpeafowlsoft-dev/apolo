<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Second-pass corrections to the legacy chart of accounts, found by re-running
 * the audit after the importer's classifier was fixed.
 *
 * Three are trade parties that the importer turned into balance-sheet accounts:
 * BABYTINO and CITY GIRL both appear as the party on an inward invoice, and
 * LUX COZY RN VEST matches six brand rows - none is capital or a bank account.
 * The other three are an expense filed as income, an interest cost held as a
 * payable, and a statutory tax filed as a trading expense.
 *
 * None carries a voucher entry. Matched on id, name and expected current group,
 * so this is a no-op anywhere the data differs; down() restores the originals.
 */
return new class extends Migration
{
    /** [id, name, from_group, to_group] */
    private array $moves = [
        // Trade parties filed as balance-sheet accounts -> Sundry Creditors
        [109, 'BABYTINO',          13, 10],
        [129, 'CITY GIRL',         13, 10],
        [529, 'LUX COZY RN VEST',   3, 10],

        // Expense filed as income -> Indirect Expenses
        [162, 'DISCOUNT EXPENSE',  18, 21],

        // Interest cost held as a payable -> Indirect Expenses
        [219, 'INTEREST EXP.',     10, 21],

        // Central Sales Tax is a statutory duty, not an overhead -> Duties & Taxes
        [141, 'CST A/C',           20, 9],
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
