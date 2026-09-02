<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('payment_attempt_id')->nullable()->unique()->after('voucher_id');
            $table->string('idempotency_key')->nullable()->unique()->after('payment_attempt_id');

            $table->foreign('payment_attempt_id')
                ->references('id')
                ->on('pos_payment_attempts')
                ->nullOnDelete();
        });

        // Add foreign key constraint for order_id on pos_payment_attempts & pos_payment_tenders & pos_print_jobs
        Schema::table('pos_payment_attempts', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::table('pos_payment_tenders', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::table('pos_print_jobs', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('pos_payment_attempts', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('pos_payment_tenders', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('pos_print_jobs', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_attempt_id']);
            $table->dropColumn(['payment_attempt_id', 'idempotency_key']);
        });
    }
};
