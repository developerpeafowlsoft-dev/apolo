<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\VatTax;
use App\Models\Salesman;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name',70);
            $table->string('barcode',11);
            $table->foreignIdFor(Category::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(SubCategory::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Brand::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(VatTax::class)->nullable()->constrained()->onDelete('set null');
            $table->decimal('buy_price', 10, 2)->default(0.00);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 10, 2)->default(0.00);
            $table->decimal('mrp', 10, 2)->default(0.00);
            $table->decimal('mark_up', 10, 2)->default(0.00);
            $table->decimal('mark_down', 10, 2)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->foreignIdFor(Salesman::class)->nullable()->constrained()->onDelete('set null');
            $table->string('commission_type',7)->nullable()->comment('1 = comm% 2 = comm₹');
            $table->decimal('salesman_comm', 10, 2)->default(0.00);
            $table->decimal('salesman_comm_amt', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_masters');
    }
};
