<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CounterMaster;
use App\Models\VatTax;
use App\Models\AccountMaster;
use App\Models\Season;
use App\Models\Agent;
use App\Models\Transport;
use App\Models\DeliveryBy;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inward_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(CounterMaster::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(VatTax::class)->nullable()->constrained()->onDelete('set null');
            $table->string('inward_voucher_no',50);
            $table->date('inward_date');
            $table->string('inward_day_name',10);
            $table->time('inward_time');
            $table->string('inward_challan_no',50);
            $table->date('inward_challan_date');
            $table->foreignId('inward_party_code')->nullable()->constrained('account_masters')->onDelete('set null');
            $table->decimal('inward_total', 10, 2)->default(0.00);
            $table->decimal('inward_party_limit', 10, 2)->default(0.00);
            $table->string('inward_credit_day', 10)->default(0);
            $table->foreignId('inward_acc_purchaser')->nullable()->constrained('account_masters')->onDelete('set null');
            $table->foreignIdFor(Season::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Agent::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Transport::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(DeliveryBy::class)->nullable()->constrained()->onDelete('set null');
            $table->string('inward_acc_lr_no',50);
            $table->date('inward_acc_lr_date');
            $table->text('inward_acc_remark');
            $table->decimal('inward_acc_gst_amount', 10, 2)->default(0.00);
            $table->decimal('inward_acc_net_amount', 10, 2)->default(0.00);
            $table->decimal('inward_acc_freight_amount', 10, 2)->default(0.00);
            $table->decimal('inward_acc_parcel_amount', 10, 2)->default(0.00);
            $table->decimal('inward_acc_amt_with_gst', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inward_invoices');
    }
};
