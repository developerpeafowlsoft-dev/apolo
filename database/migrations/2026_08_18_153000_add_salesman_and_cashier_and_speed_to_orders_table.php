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
            if (!Schema::hasColumn('orders', 'salesman_id')) {
                $table->foreignId('salesman_id')->nullable()->after('customer_id')->constrained('salesmans')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'cashier_id')) {
                $table->foreignId('cashier_id')->nullable()->after('salesman_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'billing_duration_seconds')) {
                $table->unsignedInteger('billing_duration_seconds')->default(0)->after('cashier_id');
            }
            if (!Schema::hasColumn('orders', 'billing_started_at')) {
                $table->timestamp('billing_started_at')->nullable()->after('billing_duration_seconds');
            }
        });

        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'salesman_id')) {
                $table->foreignId('salesman_id')->nullable()->after('product_id')->constrained('salesmans')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            if (Schema::hasColumn('order_products', 'salesman_id')) {
                $table->dropForeign(['salesman_id']);
                $table->dropColumn('salesman_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'salesman_id')) {
                $table->dropForeign(['salesman_id']);
                $table->dropColumn('salesman_id');
            }
            if (Schema::hasColumn('orders', 'cashier_id')) {
                $table->dropForeign(['cashier_id']);
                $table->dropColumn('cashier_id');
            }
            if (Schema::hasColumn('orders', 'billing_duration_seconds')) {
                $table->dropColumn('billing_duration_seconds');
            }
            if (Schema::hasColumn('orders', 'billing_started_at')) {
                $table->dropColumn('billing_started_at');
            }
        });
    }
};
