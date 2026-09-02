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
        if (Schema::hasTable('inward_invoices') && !Schema::hasColumn('inward_invoices', 'is_kachi')) {
            Schema::table('inward_invoices', function (Blueprint $table) {
                $table->tinyInteger('is_kachi')->default(0)->after('is_return');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inward_invoices') && Schema::hasColumn('inward_invoices', 'is_kachi')) {
            Schema::table('inward_invoices', function (Blueprint $table) {
                $table->dropColumn('is_kachi');
            });
        }
    }
};
