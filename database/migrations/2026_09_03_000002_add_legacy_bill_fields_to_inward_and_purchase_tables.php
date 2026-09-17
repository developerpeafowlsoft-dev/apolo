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
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->string('cash_or_credit', 20)->default('Credit')->nullable()->after('inward_party_limit');
            $table->decimal('bank_cash_discount_percent', 8, 4)->default(0)->nullable()->after('inward_acc_lr_date');
            $table->decimal('gross_amount', 15, 2)->default(0)->nullable()->after('inward_acc_gst_amount');
            $table->decimal('bill_discount_percent', 8, 4)->default(0)->nullable()->after('gross_amount');
            $table->decimal('bill_discount_amount', 15, 2)->default(0)->nullable()->after('bill_discount_percent');
            $table->decimal('cash_discount_percent', 8, 4)->default(0)->nullable()->after('bill_discount_amount');
            $table->decimal('cash_discount_amount', 15, 2)->default(0)->nullable()->after('cash_discount_percent');
            $table->decimal('agent_commission_percent', 8, 4)->default(0)->nullable()->after('cash_discount_amount');
            $table->decimal('agent_commission_amount', 15, 2)->default(0)->nullable()->after('agent_commission_percent');
            $table->decimal('expense_amount', 15, 2)->default(0)->nullable()->after('agent_commission_amount');
            $table->decimal('other_amount', 15, 2)->default(0)->nullable()->after('expense_amount');
        });

        Schema::table('product_purchases', function (Blueprint $table) {
            $table->string('cash_or_credit', 20)->default('Credit')->nullable()->after('bill_date');
            $table->decimal('bank_cash_discount_percent', 8, 4)->default(0)->nullable()->after('cash_or_credit');
            $table->decimal('gross_amount', 15, 2)->default(0)->nullable()->after('total_taxable');
            $table->decimal('bill_discount_percent', 8, 4)->default(0)->nullable()->after('gross_amount');
            $table->decimal('bill_discount_amount', 15, 2)->default(0)->nullable()->after('bill_discount_percent');
            $table->decimal('cash_discount_percent', 8, 4)->default(0)->nullable()->after('bill_discount_amount');
            $table->decimal('cash_discount_amount', 15, 2)->default(0)->nullable()->after('cash_discount_percent');
            $table->decimal('agent_commission_percent', 8, 4)->default(0)->nullable()->after('cash_discount_amount');
            $table->decimal('agent_commission_amount', 15, 2)->default(0)->nullable()->after('agent_commission_percent');
            $table->decimal('expense_amount', 15, 2)->default(0)->nullable()->after('agent_commission_amount');
            $table->decimal('other_amount', 15, 2)->default(0)->nullable()->after('expense_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'cash_or_credit',
                'bank_cash_discount_percent',
                'gross_amount',
                'bill_discount_percent',
                'bill_discount_amount',
                'cash_discount_percent',
                'cash_discount_amount',
                'agent_commission_percent',
                'agent_commission_amount',
                'expense_amount',
                'other_amount',
            ]);
        });

        Schema::table('product_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'cash_or_credit',
                'bank_cash_discount_percent',
                'gross_amount',
                'bill_discount_percent',
                'bill_discount_amount',
                'cash_discount_percent',
                'cash_discount_amount',
                'agent_commission_percent',
                'agent_commission_amount',
                'expense_amount',
                'other_amount',
            ]);
        });
    }
};
