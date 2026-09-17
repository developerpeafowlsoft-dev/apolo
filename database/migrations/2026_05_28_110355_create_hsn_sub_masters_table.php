<?php

use App\Models\HsnMaster;
use App\Models\VatTax;
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
        Schema::create('hsn_sub_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HsnMaster::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(VatTax::class)->constrained()->onDelete('restrict');
            $table->decimal('from_sales_rate', 16, 2)->default(0.00);
            $table->decimal('to_sales_rate', 16, 2)->default(0.00);
            $table->decimal('to_purchase_rate', 16, 2)->default(0.00);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hsn_sub_masters');
    }
};
