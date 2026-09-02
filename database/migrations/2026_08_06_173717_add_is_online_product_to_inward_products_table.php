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
        if (!Schema::hasColumn('inward_products', 'is_online_product')) {
            Schema::table('inward_products', function (Blueprint $table) {
                $table->boolean('is_online_product')->default(false)->after('is_active');
            });
        }

        // Populate existing inward products: mark as online if barcode exists and parent product is online
        $inwardProducts = \DB::table('inward_products')->get();
        foreach ($inwardProducts as $ip) {
            $hasBarcode = \DB::table('product_barcodes')->where('inward_product_id', $ip->id)->exists();
            $isProductOnline = false;
            if ($ip->design_master_id) {
                $isProductOnline = \DB::table('products')
                    ->where('design_master_id', $ip->design_master_id)
                    ->where('is_item_master', 0)
                    ->where('is_online_product', 1)
                    ->exists();
            }
            if ($hasBarcode && $isProductOnline) {
                \DB::table('inward_products')->where('id', $ip->id)->update(['is_online_product' => 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inward_products', 'is_online_product')) {
            Schema::table('inward_products', function (Blueprint $table) {
                $table->dropColumn('is_online_product');
            });
        }
    }
};
