<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AccountGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('bank_name');
            $table->string('short_name',11);
            $table->foreignIdFor(AccountGroup::class)->constrained()->onDelete('restrict');
            $table->string('bank_ac_no', 20);
            $table->string('bank_branch', 170);
            $table->string('bank_swift_code', 12);
            $table->string('bank_ifsc_code', 12);
            $table->text('bank_address');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_masters');
    }
};
