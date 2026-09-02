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
        Schema::table('order_products', function (Blueprint $table) {
            $table->decimal('mrp', 10, 2)->nullable()->after('price');
            $table->decimal('discount', 5, 2)->default(0)->after('mrp');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount');

            // ✅ Tax fields
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_percentage');
            $table->string('vat_tax_name')->nullable()->after('tax_amount');

            // ✅ Inward tracking
            $table->unsignedBigInteger('inward_invoice_id')->nullable()->after('vat_tax_name');
            $table->unsignedBigInteger('inward_product_id')->nullable()->after('inward_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn([
                'mrp', 'discount', 'discount_amount',
                'tax_percentage', 'tax_amount', 'vat_tax_name',
                'inward_invoice_id', 'inward_product_id'
            ]);
        });
    }
};
