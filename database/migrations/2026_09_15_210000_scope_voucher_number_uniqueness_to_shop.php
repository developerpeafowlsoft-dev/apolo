<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Voucher numbers are issued per shop but were unique globally.
 *
 * `voucher_sequences` is keyed on (shop_id, financial_year_id, voucher_type,
 * branch_id), so every shop starts its own series at 1. The unique index on
 * `vouchers.voucher_no` covered the column alone, so the second shop to post a
 * given type hit a duplicate-key error and lost the sale. Shop 14's `Purchase`
 * sequence carries no prefix and is already at 8, so 000001-000008 are taken
 * globally today.
 *
 * Branches inside a shop stay distinct through `voucher_range_start/end`, which
 * seeds each branch's sequence into its own band - so the shop is the right
 * boundary, not the branch.
 */
return new class extends Migration
{
    public function up(): void
    {
        $clash = DB::table('vouchers')
            ->select('shop_id', 'voucher_no', DB::raw('COUNT(*) as c'))
            ->groupBy('shop_id', 'voucher_no')
            ->having('c', '>', 1)
            ->count();

        if ($clash > 0) {
            throw new RuntimeException(
                "Cannot scope voucher numbering: {$clash} (shop_id, voucher_no) pair(s) already duplicate. Resolve them first."
            );
        }

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique('vouchers_voucher_no_unique');
            $table->unique(['shop_id', 'voucher_no'], 'vouchers_shop_voucher_no_unique');
        });

        echo "  voucher_no uniqueness scoped to (shop_id, voucher_no)\n";
    }

    public function down(): void
    {
        $clash = DB::table('vouchers')
            ->select('voucher_no', DB::raw('COUNT(*) as c'))
            ->groupBy('voucher_no')
            ->having('c', '>', 1)
            ->count();

        if ($clash > 0) {
            throw new RuntimeException(
                "Cannot restore global voucher numbering: {$clash} voucher_no value(s) are now shared between shops."
            );
        }

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique('vouchers_shop_voucher_no_unique');
            $table->unique('voucher_no', 'vouchers_voucher_no_unique');
        });
    }
};
