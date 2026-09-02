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

            $table->dropForeign(['inward_acc_purchaser']);

            $table->foreign('inward_acc_purchaser')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {

            $table->dropForeign(['inward_acc_purchaser']);

            $table->foreign('inward_acc_purchaser')
                ->references('id')
                ->on('account_masters')
                ->onDelete('set null');
        });
    }
};
