<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status'); // PosPrintJobStatus: pending, processing, printed, failed
            $table->longText('print_payload'); // receipt details
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('pos_print_jobs');
    }
};
