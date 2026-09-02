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
        Schema::create('hold_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->unsignedBigInteger('user_id'); // Cashier
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('hold_no')->unique();
            $table->double('subtotal', 15, 2)->default(0);
            $table->double('discount', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->double('payable_amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->text('remarks')->nullable();
            $table->json('tax_details')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('status')->default('active'); // active, restored
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->foreign('counter_id')->references('id')->on('counter_masters')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('salesman_id')->references('id')->on('salesmans')->nullOnDelete();

            $table->index(['shop_id', 'status']);
            $table->index('hold_no');
        });

        Schema::create('hold_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hold_bill_id');
            $table->unsignedBigInteger('product_id');
            $table->string('barcode');
            $table->double('qty', 15, 2)->default(1);
            $table->double('rate', 15, 2)->default(0);
            $table->double('disc_percent', 15, 2)->default(0);
            $table->double('disc_amt', 15, 2)->default(0);
            $table->double('tax_percent', 15, 2)->default(0);
            $table->double('tax_amt', 15, 2)->default(0);
            $table->string('tax_name')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->timestamps();

            $table->foreign('hold_bill_id')->references('id')->on('hold_bills')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('salesman_id')->references('id')->on('salesmans')->nullOnDelete();

            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hold_bill_items');
        Schema::dropIfExists('hold_bills');
    }
};
