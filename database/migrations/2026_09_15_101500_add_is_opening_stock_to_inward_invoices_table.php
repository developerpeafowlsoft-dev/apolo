<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->boolean('is_opening_stock')->default(0)->after('is_purchase');
            $table->index(['shop_id', 'is_opening_stock'], 'idx_inward_shop_opening_stock');
        });
    }

    public function down(): void
    {
        Schema::table('inward_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_inward_shop_opening_stock');
            $table->dropColumn('is_opening_stock');
        });
    }
};
