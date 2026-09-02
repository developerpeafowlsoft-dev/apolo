<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_balances', function (Blueprint $table) {
            $table->index(['shop_id', 'account_id', 'financial_year_id'], 'idx_bal_shop_ac_year');
        });

        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->index(['account_id', 'voucher_id'], 'idx_entry_ac_voucher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_balances', function (Blueprint $table) {
            $table->dropIndex('idx_bal_shop_ac_year');
        });

        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->dropIndex('idx_entry_ac_voucher');
        });
    }
};
