<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('voucher_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained()->onDelete('cascade');
            $table->string('voucher_type');
            $table->string('prefix')->nullable(); // e.g., BR01-FY2425-SAL-
            $table->unsignedTinyInteger('padding')->default(6); // e.g., 000001
            $table->unsignedBigInteger('current_no')->default(0);
            $table->enum('reset_policy', ['yearly'])->default('yearly');
            $table->timestamps();

            $table->unique(['shop_id','financial_year_id','voucher_type'], 'uniq_seq_per_type');
        });
    }

    public function down(): void {
        Schema::dropIfExists('voucher_sequences');
    }
};
