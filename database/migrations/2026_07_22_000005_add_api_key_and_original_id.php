<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('api_key')->nullable()->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('original_id')->nullable()->after('id');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('original_id')->nullable()->after('id');
        });

        Schema::table('product_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('original_id')->nullable()->after('id');
        });
    }

    public function down(): void {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('api_key');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('original_id');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('original_id');
        });

        Schema::table('product_purchases', function (Blueprint $table) {
            $table->dropColumn('original_id');
        });
    }
};
