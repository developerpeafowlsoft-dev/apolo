<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_terminal_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('pos_payment_attempt_id');
            $table->string('event_type'); // log, state_change, error
            $table->string('status_from');
            $table->string('status_to');
            $table->text('payload')->nullable(); // encrypted raw data
            $table->timestamps();

            $table->foreign('pos_payment_attempt_id')
                ->references('id')
                ->on('pos_payment_attempts')
                ->cascadeOnDelete();

            $table->index(['pos_payment_attempt_id', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('pos_terminal_events');
    }
};
