<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Product::class)->nullable()->constrained('products')->onDelete('set null');
            $table->foreignIdFor(InwardInvoice::class)->constrained('inward_invoices')->cascadeOnDelete();
            $table->foreignIdFor(InwardProduct::class)->constrained('inward_products')->cascadeOnDelete();
            $table->string('barcode_number')->unique();
            $table->decimal('mrp',10,2)->nullable();
            $table->boolean('is_printed')->default(0);
            $table->boolean('is_sold')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
    }
};
