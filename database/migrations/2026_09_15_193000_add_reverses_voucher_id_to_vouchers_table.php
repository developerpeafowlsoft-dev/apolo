<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a reversing (contra) voucher back to the voucher it reverses.
 *
 * Reversal used to adjust account_balances directly and flip a status flag,
 * leaving the original entries in place - and no report filters on status, so a
 * reversed voucher stayed in the trial balance, P&L and balance sheet while its
 * effect had already been removed from balances. Posting a mirrored contra
 * voucher instead nets the original to zero in any report that sums entries,
 * with no report changes and a proper audit trail.
 *
 * `original_id` was not reused: the migration engine already stores legacy
 * voucher numbers there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('reverses_voucher_id')->nullable()->after('original_id');
            $table->index('reverses_voucher_id', 'idx_voucher_reverses');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_voucher_reverses');
            $table->dropColumn('reverses_voucher_id');
        });
    }
};
