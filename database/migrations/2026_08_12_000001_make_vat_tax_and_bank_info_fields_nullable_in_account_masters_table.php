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
            $table->unsignedBigInteger('vat_tax_id')->nullable()->change();
            $table->string('bank_info_swift_code', 70)->nullable()->change();
            $table->string('bank_info_upi_id', 255)->nullable()->change();
            $table->text('bank_info_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_masters', function (Blueprint $table) {
            $table->unsignedBigInteger('vat_tax_id')->nullable(false)->change();
            $table->string('bank_info_swift_code', 70)->nullable(false)->change();
            $table->string('bank_info_upi_id', 255)->nullable(false)->change();
            $table->text('bank_info_address')->nullable(false)->change();
        });
    }
};
