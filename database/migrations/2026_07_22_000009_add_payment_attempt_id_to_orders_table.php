<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_payment_tenders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // foreign key constraint added after orders migration update
            $table->uuid('pos_payment_attempt_id');
            $table->string('payment_method'); // cash, card, upi
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('pos_payment_attempt_id')
                ->references('id')
                ->on('pos_payment_attempts')
                ->cascadeOnDelete();

            $table->index(['order_id', 'pos_payment_attempt_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('pos_payment_tenders');
    }
};
