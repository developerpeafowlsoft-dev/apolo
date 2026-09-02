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
        Schema::table('products', function (Blueprint $table) {
            $table->json('inward_invoice_ids')->nullable()->after('design_master_id');

            // ✅ Drop old single column if exists
            if (Schema::hasColumn('products', 'inward_invoice_id')) {
                $table->dropColumn('inward_invoice_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('inward_invoice_ids');

            // ✅ Restore old column
            $table->unsignedBigInteger('inward_invoice_id')->nullable()->after('design_master_id');
        });
    }
};
