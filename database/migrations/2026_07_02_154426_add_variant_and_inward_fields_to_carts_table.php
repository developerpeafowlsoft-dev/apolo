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
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('quantity');
            $table->decimal('mrp', 10, 2)->nullable()->after('price');
            $table->decimal('discount', 5, 2)->default(0)->after('mrp');

            $table->unsignedBigInteger('inward_invoice_id')->nullable()->after('discount');
            $table->unsignedBigInteger('inward_product_id')->nullable()->after('inward_invoice_id');

            // ✅ Foreign keys (optional - for data integrity)
            $table->foreign('inward_invoice_id')
                ->references('id')
                ->on('inward_invoices')
                ->onDelete('set null');

            $table->foreign('inward_product_id')
                ->references('id')
                ->on('inward_products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['inward_invoice_id']);
            $table->dropForeign(['inward_product_id']);
            $table->dropColumn(['price', 'mrp', 'discount', 'inward_invoice_id', 'inward_product_id']);
        });
    }
};
