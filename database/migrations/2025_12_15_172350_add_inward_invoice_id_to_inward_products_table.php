<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\InwardInvoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inward_products', function (Blueprint $table) {
            $table->foreignIdFor(InwardInvoice::class)->nullable()->after('shop_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inward_products', function (Blueprint $table) {
            $table->dropForeign(['inward_invoice_id']);
            $table->dropColumn('inward_invoice_id');
        });
    }
};
