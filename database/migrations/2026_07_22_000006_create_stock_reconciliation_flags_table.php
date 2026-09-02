<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_reconciliation_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('reported_sale_qty');
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->string('reason');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_reconciliation_flags');
    }
};
