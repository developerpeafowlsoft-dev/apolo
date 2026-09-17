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
        if (Schema::hasTable('account_masters') && !Schema::hasColumn('account_masters', 'is_party_code')) {
            Schema::table('account_masters', function (Blueprint $table) {
                $table->boolean('is_party_code')->default(1)->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_masters') && Schema::hasColumn('account_masters', 'is_party_code')) {
            Schema::table('account_masters', function (Blueprint $table) {
                $table->dropColumn('is_party_code');
            });
        }
    }
};
