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
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('address')->constrained('countries')->nullOnDelete();
            }
            if (!Schema::hasColumn('shops', 'state_id')) {
                $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->nullOnDelete();
            }
            if (!Schema::hasColumn('shops', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('state_id')->constrained('cities')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'city_id')) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            }
            if (Schema::hasColumn('shops', 'state_id')) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            }
            if (Schema::hasColumn('shops', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }
        });
    }
};
