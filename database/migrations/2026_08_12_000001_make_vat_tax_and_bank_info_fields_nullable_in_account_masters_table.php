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
            $table->string('bank_info_ifsc_code', 50)->nullable()->change();
            $table->string('tax_info_gst_no', 50)->nullable()->change();
            $table->string('tax_info_pan_no', 50)->nullable()->change();
            $table->string('tax_info_tan_no', 50)->nullable()->change();
            $table->string('accountshortcode', 50)->nullable()->change();
            $table->string('cont_info_mobile1', 50)->nullable()->change();
            $table->string('cont_info_mobile2', 50)->nullable()->change();
            $table->string('cont_info_phone', 50)->nullable()->change();
            $table->string('bank_info_ac_no', 50)->nullable()->change();
            $table->string('bank_info_bank_name', 255)->nullable()->change();
            $table->string('bank_info_branch', 255)->nullable()->change();
            $table->string('bank_info_payment_name', 255)->nullable()->change();
            $table->unsignedBigInteger('city_id')->nullable()->change();
            $table->unsignedBigInteger('cities_id')->nullable()->change();
            $table->unsignedBigInteger('state_id')->nullable()->change();
            $table->unsignedBigInteger('country_id')->nullable()->change();
            $table->string('contpincode', 255)->nullable()->change();
            $table->string('contperson', 255)->nullable()->change();
            $table->text('contaddress')->nullable()->change();
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
