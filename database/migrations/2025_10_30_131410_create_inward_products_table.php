<?php

use App\Models\DesignMaster;
use App\Models\Product;
use App\Models\HsnMaster;
use App\Models\VatTax;
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
        Schema::create('inward_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('design_master_id')->constrained('design_masters')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->float('buy_price')->nullable()->default(0);
            $table->float('price');
            $table->float('discount_price')->nullable();
            $table->float('mrp');
            $table->decimal('mark_up', 10, 2)->default(0.00);
            $table->decimal('mark_down', 10, 2)->default(0.00);
            $table->float('net_purc_rate');
            $table->foreignId('hsn_master_id')->comment('Tax Code')->constrained('hsn_masters');
            $table->foreignId('vat_tax_id')->comment('SGST')->constrained('vat_taxes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inward_products');
    }
};
