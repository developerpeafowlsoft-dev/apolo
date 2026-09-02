<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_terminals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained('counter_masters')->nullOnDelete();
            $table->string('name');
            $table->string('provider'); // paytm, phonepe, mock
            $table->string('terminal_id')->unique();
            $table->string('merchant_id')->nullable();
            $table->text('config_data')->nullable(); // encrypted config options
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // operationally safe soft delete

            $table->index(['shop_id', 'is_active']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('payment_terminals');
    }
};
