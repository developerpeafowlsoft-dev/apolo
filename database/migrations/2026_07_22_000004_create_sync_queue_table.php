<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->string('syncable_type');
            $table->unsignedBigInteger('syncable_id');
            $table->json('payload');
            $table->enum('status', ['pending', 'syncing', 'synced', 'failed'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'attempts']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('sync_queue');
    }
};
