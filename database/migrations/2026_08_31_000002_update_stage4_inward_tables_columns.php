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
        if (Schema::hasTable('product_barcodes')) {
            Schema::table('product_barcodes', function (Blueprint $table) {
                $table->unsignedBigInteger('inward_invoice_id')->nullable()->change();
                $table->unsignedBigInteger('inward_product_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('inward_products')) {
            Schema::table('inward_products', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->change();
                $table->unsignedBigInteger('design_master_id')->nullable()->change();
                $table->double('price')->default(0)->change();
                $table->double('mrp')->default(0)->change();
                $table->double('net_purc_rate')->default(0)->change();
                $table->unsignedBigInteger('hsn_master_id')->nullable()->change();
                $table->unsignedBigInteger('vat_tax_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
