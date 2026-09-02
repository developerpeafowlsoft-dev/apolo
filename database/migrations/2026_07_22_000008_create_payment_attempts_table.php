<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_payment_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('payment_terminal_id')->nullable()->constrained('payment_terminals')->nullOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('pos_shifts')->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // foreign key constraint added after orders table update
            $table->string('cart_name');
            $table->string('provider'); // paytm, phonepe, mock
            $table->string('payment_method'); // card, upi
            $table->decimal('amount', 15, 2); // requested amount
            $table->decimal('approved_amount', 15, 2)->default(0.00); // approved amount
            $table->string('status'); // PosPaymentAttemptStatus values
            $table->string('transaction_id')->nullable(); // unique provider reference
            $table->string('reference_no')->nullable(); // RRN / audit no
            $table->string('terminal_id')->nullable(); // active terminal serial
            $table->string('card_type')->nullable(); // VISA/MASTERCARD
            $table->string('masked_pan')->nullable();
            $table->text('response_payload')->nullable(); // encrypted responses
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // operationally safe

            // Composite indexes and unique constraints
            $table->index(['shop_id', 'status', 'created_at']);
            $table->index(['status', 'transaction_id']);
            
            // Unique provider transaction reference (non-nulls must be unique)
            $table->unique(['provider', 'transaction_id']);
        });

        // Add CHECK constraints to verify non-negative amounts
        DB::statement('ALTER TABLE pos_payment_attempts ADD CONSTRAINT chk_amount_non_negative CHECK (amount >= 0)');
        DB::statement('ALTER TABLE pos_payment_attempts ADD CONSTRAINT chk_approved_amount_non_negative CHECK (approved_amount >= 0)');
    }

    public function down(): void {
        Schema::dropIfExists('pos_payment_attempts');
    }
};
