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
        Schema::create('pos_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->unsignedBigInteger('original_order_id')->nullable();
            $table->string('return_no');
            $table->unsignedBigInteger('customer_id');
            $table->double('total_amount', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->foreign('counter_id')->references('id')->on('counter_masters')->nullOnDelete();
            $table->foreign('original_order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('cashier_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('pos_return_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('product_id');
            $table->string('barcode_number');
            $table->integer('qty')->default(1);
            $table->double('rate', 15, 2)->default(0);
            $table->double('tax_amt', 15, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('pos_returns')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_return_products');
        Schema::dropIfExists('pos_returns');
    }
};
