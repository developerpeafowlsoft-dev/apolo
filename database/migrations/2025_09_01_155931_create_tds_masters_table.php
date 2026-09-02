<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tds_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('tds_code',4);
            $table->text('tds_description');
            $table->foreignId('tds_payable_id')->constrained('account_masters')->onDelete('restrict');
            $table->foreignId('tds_receivable_id')->constrained('account_masters')->onDelete('restrict');
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('tds_percentage', 10, 2)->default(0.00);
            $table->integer('tds_limit')->default(0);
            $table->integer('tds_single_trans_limit')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tds_masters');
    }
};
