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
            if (!Schema::hasColumn('shops', 'gemini_model')) {
                $table->string('gemini_model', 100)->nullable()->default('gemini-1.5-flash')->after('gemini_api_key');
            }
        });

        Schema::table('generate_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('generate_settings', 'gemini_model')) {
                $table->string('gemini_model', 100)->nullable()->default('gemini-1.5-flash')->after('gemini_api_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'gemini_model')) {
                $table->dropColumn('gemini_model');
            }
        });

        Schema::table('generate_settings', function (Blueprint $table) {
            if (Schema::hasColumn('generate_settings', 'gemini_model')) {
                $table->dropColumn('gemini_model');
            }
        });
    }
};
