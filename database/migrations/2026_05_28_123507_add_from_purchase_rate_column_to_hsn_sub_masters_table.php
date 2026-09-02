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
        Schema::table('hsn_sub_masters', function (Blueprint $table) {
            $table->decimal('from_purchase_rate', 10, 2)->after('to_sales_rate')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hsn_sub_masters', function (Blueprint $table) {
            $table->dropColumn('from_purchase_rate');
        });
    }
};
