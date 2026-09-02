<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('reconciliation_date');
            $table->string('provider'); // paytm, phonepe
            $table->integer('total_attempts_count');
            $table->decimal('total_attempted_amount', 15, 2);
            $table->decimal('total_settled_amount', 15, 2);
            $table->decimal('discrepancy_amount', 15, 2)->default(0.00);
            $table->string('status'); // balanced, unmatched
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'reconciliation_date', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('payment_reconciliations');
    }
};
