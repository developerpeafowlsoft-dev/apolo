<?php

use App\Models\InwardInvoice;
use App\Models\InwardProduct;
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
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(InwardInvoice::class)->constrained()->cascadeOnDelete();
            $table->date('purchase_date')->nullable();
            $table->string('purchase_day_name',10)->nullable();
            $table->time('purchase_time')->nullable();
            $table->date('bill_date')->nullable();
            $table->boolean('is_purchase')->default(0);
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
    }
};
