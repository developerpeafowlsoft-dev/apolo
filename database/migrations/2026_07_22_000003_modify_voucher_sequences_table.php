<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('voucher_sequences', function (Blueprint $table) {
            // Create explicit indexes for the foreign key columns so MySQL doesn't block dropping the composite unique index
            $table->index('shop_id', 'voucher_sequences_shop_id_idx');
            $table->index('financial_year_id', 'voucher_sequences_financial_year_id_idx');

            $table->dropUnique('uniq_seq_per_type');

            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            $table->unsignedBigInteger('range_start')->nullable()->after('padding');
            $table->unsignedBigInteger('range_end')->nullable()->after('range_start');

            $table->unique(['shop_id', 'financial_year_id', 'voucher_type', 'branch_id'], 'uniq_seq_per_type_branch');
        });
    }

    public function down(): void {
        Schema::table('voucher_sequences', function (Blueprint $table) {
            $table->dropUnique('uniq_seq_per_type_branch');
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'range_start', 'range_end']);
            
            $table->unique(['shop_id', 'financial_year_id', 'voucher_type'], 'uniq_seq_per_type');

            $table->dropIndex('voucher_sequences_shop_id_idx');
            $table->dropIndex('voucher_sequences_financial_year_id_idx');
        });
    }
};
