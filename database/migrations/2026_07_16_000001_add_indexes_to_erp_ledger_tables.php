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
            $table->index(['shop_id', 'financial_year_id'], 'idx_balance_shop_year');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->index(['shop_id', 'financial_year_id'], 'idx_voucher_shop_year');
        });

        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->index('inward_voucher_no', 'idx_inward_voucher_no');
            $table->index('inward_challan_no', 'idx_inward_challan_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_balances', function (Blueprint $table) {
            $table->dropIndex('idx_balance_shop_year');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_voucher_shop_year');
        });

        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_inward_voucher_no');
            $table->dropIndex('idx_inward_challan_no');
        });
    }
};
