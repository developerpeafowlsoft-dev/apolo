<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carries the legacy inward voucher's detail onto opening stock.
 *
 * The first opening-stock import read only "Barcode Search P.xlsx", which knows
 * an item's cost and markup but nothing about the challan it arrived on: no
 * sales rate, no tax name, no GST split, no supplier GSTIN, and no link to the
 * voucher itself. "Voucher Detail Wise INWARD P.xlsx" carries all of that, and
 * the two join on (financial year, voucher no, party) - the year being essential
 * because legacy voucher numbers restart at 00001 every April.
 *
 * Every column is nullable on purpose. Three financial years ship only a
 * voucher-wise inward export with no per-item rows, and FY 2016-17 ships no
 * inward file at all; for those a NULL means "the source does not carry this",
 * which is worth distinguishing from a real zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inward_products', function (Blueprint $table) {
            // From the inward challan line.
            $table->string('tax_name', 50)->nullable()->after('vat_tax_id');
            $table->decimal('sgst_amount', 12, 2)->nullable()->after('tax_name');
            $table->decimal('cgst_amount', 12, 2)->nullable()->after('sgst_amount');
            $table->decimal('igst_amount', 12, 2)->nullable()->after('cgst_amount');
            $table->decimal('inward_sales_rate', 12, 2)->nullable()->after('igst_amount');
            $table->decimal('inward_net_amount', 14, 2)->nullable()->after('inward_sales_rate');

            // From the barcode snapshot - cost components the challan does not carry.
            $table->decimal('purcost_rate', 12, 2)->nullable()->after('inward_net_amount');
            $table->decimal('pur_exp_rate', 12, 2)->nullable()->after('purcost_rate');

            // How this line found its challan line, so a later reader can tell a
            // sourced figure from a fallback one.
            $table->string('legacy_line_match', 24)->nullable()->after('pur_exp_rate');
        });

        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->string('legacy_financial_year', 9)->nullable()->after('is_opening_stock');
            $table->string('legacy_party_gstin', 20)->nullable()->after('legacy_financial_year');
            // inward_detail | inward_summary | snapshot_only
            $table->string('legacy_source', 20)->nullable()->after('legacy_party_gstin');
            $table->boolean('legacy_voucher_resolved')->default(false)->after('legacy_source');

            $table->index(['shop_id', 'legacy_financial_year'], 'idx_inward_shop_legacy_fy');
            $table->index(['shop_id', 'legacy_voucher_resolved'], 'idx_inward_shop_legacy_resolved');
        });
    }

    public function down(): void
    {
        Schema::table('inward_products', function (Blueprint $table) {
            $table->dropColumn([
                'tax_name', 'sgst_amount', 'cgst_amount', 'igst_amount',
                'inward_sales_rate', 'inward_net_amount',
                'purcost_rate', 'pur_exp_rate', 'legacy_line_match',
            ]);
        });

        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_inward_shop_legacy_fy');
            $table->dropIndex('idx_inward_shop_legacy_resolved');
            $table->dropColumn([
                'legacy_financial_year', 'legacy_party_gstin',
                'legacy_source', 'legacy_voucher_resolved',
            ]);
        });
    }
};
