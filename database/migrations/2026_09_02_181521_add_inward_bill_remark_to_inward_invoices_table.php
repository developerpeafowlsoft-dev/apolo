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
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->text('inward_bill_remark')->nullable()->after('inward_acc_amt_with_gst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->dropColumn('inward_bill_remark');
        });
    }
};
