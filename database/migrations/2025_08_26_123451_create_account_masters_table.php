<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\VatTax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('accountName');
            $table->foreignId('account_id')->constrained()->onDelete('restrict');
            $table->foreignId('cities_id')->constrained()->onDelete('restrict');
            $table->string('accountshortcode',6);
            $table->decimal('agentcomm', 10, 2)->default(0.00);
            $table->string('agentcommremark')->nullable();
            $table->string('referenceby')->nullable();

            $table->string('contperson');
            $table->string('contpincode');
            $table->text('contaddress');
            $table->foreignIdFor(Country::class)->constrained()->onDelete('restrict');
            $table->foreignIdFor(State::class)->constrained()->onDelete('restrict');
            $table->foreignIdFor(City::class)->constrained()->onDelete('restrict');

            $table->string('cont_info_mobile1',13);
            $table->string('cont_info_mobile2',13)->nullable();
            $table->string('cont_info_phone',13)->nullable();
            $table->boolean('cont_info_send_sms')->default(0);
            $table->boolean('cont_info_dndactivate')->default(0);
            $table->string('cont_info_email')->nullable();
            $table->string('cont_info_website_url')->nullable();
            $table->date('cont_info_birth_date')->nullable();

            $table->string('tax_info_gst_no', 15);
            $table->date('tax_info_gst_reg_date')->nullable();
            $table->date('tax_info_gst_cancel_date')->nullable();
            $table->foreignIdFor(VatTax::class)->nullable()->constrained()->onDelete('restrict');
            $table->string('tax_info_tan_no', 10)->nullable();
            $table->string('tax_info_pan_no', 20);
            $table->boolean('tax_info_tds_deduct')->default(0);
            $table->boolean('tax_info_tcs_deduct')->default(0);

            $table->string('bank_info_bank_name', 255);
            $table->string('bank_info_ac_no', 20);
            $table->string('bank_info_swift_code', 70)->nullable();
            $table->string('bank_info_ifsc_code', 11);
            $table->string('bank_info_branch', 255);
            $table->string('bank_info_upi_id', 255)->nullable();
            $table->string('bank_info_payment_name', 255);
            $table->text('bank_info_address')->nullable();

            $table->decimal('other_info_discount', 10, 2)->nullable()->default(0.00);
            $table->decimal('other_info_discount_limit', 10, 2)->nullable()->default(0.00);
            $table->decimal('other_info_cash_disc', 10, 2)->nullable()->default(0.00);
            $table->decimal('other_info_special_disc', 10, 2)->nullable()->default(0.00);
            $table->decimal('other_info_bank_cs_disc', 10, 2)->nullable()->default(0.00);
            $table->integer('other_info_credit_day')->nullable()->default(0);
            $table->decimal('other_info_act_limit', 10, 2)->nullable()->default(0.00);
            $table->string('other_info_adjustment_type')->nullable();
            $table->string('other_info_delivery_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_masters');
    }
};
