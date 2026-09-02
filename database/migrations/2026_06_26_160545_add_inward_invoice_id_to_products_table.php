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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('inward_invoice_id')->nullable()->after('design_master_id');
            $table->foreign('inward_invoice_id')
                ->references('id')
                ->on('inward_invoices')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['inward_invoice_id']);
            $table->dropColumn('inward_invoice_id');
        });
    }
};
