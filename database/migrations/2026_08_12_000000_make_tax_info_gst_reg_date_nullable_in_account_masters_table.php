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
        Schema::table('account_masters', function (Blueprint $table) {
            $table->date('tax_info_gst_reg_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_masters', function (Blueprint $table) {
            $table->date('tax_info_gst_reg_date')->nullable(false)->change();
        });
    }
};
