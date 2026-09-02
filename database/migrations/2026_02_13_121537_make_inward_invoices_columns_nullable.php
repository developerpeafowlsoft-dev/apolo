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
            $table->string('inward_voucher_no',50)->nullable()->change();
            $table->date('inward_date')->nullable()->change();
            $table->string('inward_day_name',10)->nullable()->change();
            $table->time('inward_time')->nullable()->change();
            $table->string('inward_challan_no',50)->nullable()->change();
            $table->date('inward_challan_date')->nullable()->change();

            $table->string('inward_acc_lr_no',50)->nullable()->change();
            $table->date('inward_acc_lr_date')->nullable()->change();
            $table->text('inward_acc_remark')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->string('inward_voucher_no',50)->nullable(false)->change();
            $table->date('inward_date')->nullable(false)->change();
            $table->string('inward_day_name',10)->nullable(false)->change();
            $table->time('inward_time')->nullable(false)->change();
            $table->string('inward_challan_no',50)->nullable(false)->change();
            $table->date('inward_challan_date')->nullable(false)->change();

            $table->string('inward_acc_lr_no',50)->nullable(false)->change();
            $table->date('inward_acc_lr_date')->nullable(false)->change();
            $table->text('inward_acc_remark')->nullable(false)->change();
        });
    }
};
