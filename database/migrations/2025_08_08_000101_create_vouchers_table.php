<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->string('voucher_type'); 
            $table->enum('status', ['draft','posted','reversed'])->default('posted');
            $table->date('date');
            $table->text('narration')->nullable();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('sequence_id');
            $table->timestamps();

            $table->index(['date', 'shop_id', 'financial_year_id']);
            $table->index(['voucher_type', 'shop_id', 'financial_year_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('vouchers');
    }
};
