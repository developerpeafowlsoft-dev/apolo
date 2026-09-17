<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every financial year was flagged is_active = 1, so any lookup of "the active
 * year" returned whichever row came first - 2016-2017, a decade stale. That is
 * how the 65 opening balances imported by Step 9 ended up filed under FY 1.
 *
 * Activates only the year covering the run date. Resolved by date rather than a
 * fixed id so the migration is correct in any environment and at any time.
 */
return new class extends Migration
{
    public function up(): void
    {
        $current = DB::table('financial_years')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->orderByDesc('start_date')
            ->first();

        if (!$current) {
            echo "  no financial year covers " . now()->toDateString() . " - leaving flags untouched\n";
            return;
        }

        $deactivated = DB::table('financial_years')
            ->where('id', '!=', $current->id)
            ->where('is_active', 1)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        DB::table('financial_years')
            ->where('id', $current->id)
            ->update(['is_active' => 1, 'updated_at' => now()]);

        echo "  active financial year: {$current->name} (id {$current->id}); deactivated {$deactivated} other(s)\n";
    }

    public function down(): void
    {
        // Prior state had every year flagged active.
        $n = DB::table('financial_years')->update(['is_active' => 1, 'updated_at' => now()]);
        echo "  restored is_active = 1 on {$n} financial year(s)\n";
    }
};
