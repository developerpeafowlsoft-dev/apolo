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
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('counter_id');
            $table->unsignedBigInteger('user_id');
            $table->double('opening_cash', 15, 2)->default(0);
            $table->double('closing_cash', 15, 2)->nullable();
            $table->double('expected_cash', 15, 2)->nullable();
            $table->double('total_sales_cash', 15, 2)->default(0);
            $table->double('total_sales_card', 15, 2)->default(0);
            $table->double('total_returns_cash', 15, 2)->default(0);
            $table->double('total_returns_card', 15, 2)->default(0);
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->double('difference', 15, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->foreign('counter_id')->references('id')->on('counter_masters')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};
