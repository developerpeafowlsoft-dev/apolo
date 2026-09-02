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
        Schema::table('product_purchases', function (Blueprint $table) {
            $table->decimal('total_taxable', 15, 2)->default(0)->after('voucher_id');
            $table->decimal('total_cgst', 15, 2)->default(0)->after('total_taxable');
            $table->decimal('total_sgst', 15, 2)->default(0)->after('total_cgst');
            $table->decimal('total_igst', 15, 2)->default(0)->after('total_sgst');
            $table->decimal('round_off', 15, 2)->default(0)->after('total_igst');
            $table->decimal('grand_total', 15, 2)->default(0)->after('round_off');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'total_taxable',
                'total_cgst',
                'total_sgst',
                'total_igst',
                'round_off',
                'grand_total'
            ]);
        });
    }
};
