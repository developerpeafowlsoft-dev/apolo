<?php

use App\Models\AccountMaster;
use App\Models\Product;
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
        Schema::create('design_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('design_number',255);
            $table->foreignIdFor(Product::class)->nullable()->comment('Item Name')->constrained()->onDelete('set null');
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->decimal('buy_price', 10, 2)->default(0.00);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 10, 2)->default(0.00);
            $table->decimal('mrp', 10, 2)->default(0.00);
            $table->decimal('mark_up', 10, 2)->default(0.00);
            $table->decimal('mark_down', 10, 2)->default(0.00);
            $table->foreignIdFor(AccountMaster::class)->nullable()->constrained()->onDelete('set null');
            $table->string('account_master_name',255);
            $table->text('remark')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_masters');
    }
};
