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
        if (Schema::hasTable('item_masters')) {
            Schema::table('item_masters', function (Blueprint $table) {
                $table->string('item_name', 255)->change();
                $table->string('barcode', 100)->nullable()->change();
            });
        }

        if (Schema::hasTable('design_masters')) {
            Schema::table('design_masters', function (Blueprint $table) {
                $table->string('account_master_name', 255)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('item_masters')) {
            Schema::table('item_masters', function (Blueprint $table) {
                $table->string('item_name', 70)->change();
                $table->string('barcode', 11)->nullable(false)->change();
            });
        }

        if (Schema::hasTable('design_masters')) {
            Schema::table('design_masters', function (Blueprint $table) {
                $table->string('account_master_name', 255)->nullable(false)->change();
            });
        }
    }
};
