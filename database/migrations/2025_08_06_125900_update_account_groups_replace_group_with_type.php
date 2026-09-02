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
        Schema::table('account_groups', function (Blueprint $table) {
            // Drop old column (if exists)
            if (Schema::hasColumn('account_groups', 'account_group_id')) {
                $table->dropForeign(['account_group_id']);
                $table->dropColumn('account_group_id');
            }

            // Add new column
            $table->foreignId('account_type_id')
                ->after('code')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
