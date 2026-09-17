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
        if (Schema::hasTable('inward_products') && !Schema::hasColumn('inward_products', 'net_purc_price')) {
            Schema::table('inward_products', function (Blueprint $table) {
                $table->decimal('net_purc_price', 12, 2)->default(0.00)->after('discount_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inward_products') && Schema::hasColumn('inward_products', 'net_purc_price')) {
            Schema::table('inward_products', function (Blueprint $table) {
                $table->dropColumn('net_purc_price');
            });
        }
    }
};
