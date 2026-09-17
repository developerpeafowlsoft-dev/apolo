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
        Schema::table('inward_products', function (Blueprint $table) {
            if (!Schema::hasColumn('inward_products', 'is_purchase_only')) {
                $table->boolean('is_purchase_only')->default(false)->after('is_online_product');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_products', function (Blueprint $table) {
            if (Schema::hasColumn('inward_products', 'is_purchase_only')) {
                $table->dropColumn('is_purchase_only');
            }
        });
    }
};
