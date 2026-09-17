<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\VatTax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hsn_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('hsn_code',15);
            $table->text('hsn_description');
            $table->foreignIdFor(VatTax::class)->constrained()->onDelete('restrict');
            $table->decimal('from_sales_rate', 16, 2)->default(0.00);
            $table->decimal('to_sales_rate', 16, 2)->default(0.00);
            $table->decimal('to_purchase_rate', 16, 2)->default(0.00);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hsn_masters');
    }
};
