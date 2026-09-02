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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shiprocket_order_id')->nullable()->after('payment_method');
            $table->string('shiprocket_shipment_id')->nullable()->after('shiprocket_order_id');
            $table->string('shiprocket_awb_code')->nullable()->after('shiprocket_shipment_id');
            $table->string('shiprocket_courier_company_id')->nullable()->after('shiprocket_awb_code');
            $table->string('shiprocket_courier_name')->nullable()->after('shiprocket_courier_company_id');
            $table->string('shiprocket_status')->nullable()->after('shiprocket_courier_name');
            $table->string('shiprocket_pickup_status')->nullable()->after('shiprocket_status');
            $table->string('shiprocket_pickup_token')->nullable()->after('shiprocket_pickup_status');
            $table->string('shiprocket_pickup_date')->nullable()->after('shiprocket_pickup_token');
            $table->json('shiprocket_tracking_data')->nullable()->after('shiprocket_pickup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'shiprocket_awb_code',
                'shiprocket_courier_company_id',
                'shiprocket_courier_name',
                'shiprocket_status',
                'shiprocket_pickup_status',
                'shiprocket_pickup_token',
                'shiprocket_pickup_date',
                'shiprocket_tracking_data',
            ]);
        });
    }
};
