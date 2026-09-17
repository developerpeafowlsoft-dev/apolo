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
        Schema::table('product_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('product_purchases', 'bill_remark')) {
                $table->text('bill_remark')->nullable()->after('other_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('product_purchases', 'bill_remark')) {
                $table->dropColumn('bill_remark');
            }
        });
    }
};
