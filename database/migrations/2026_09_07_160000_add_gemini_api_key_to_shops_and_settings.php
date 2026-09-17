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
            if (!Schema::hasColumn('shops', 'gemini_api_key')) {
                $table->text('gemini_api_key')->nullable()->after('description');
            }
        });

        Schema::table('generate_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('generate_settings', 'gemini_api_key')) {
                $table->text('gemini_api_key')->nullable()->after('shiprocket_pickup_pincode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'gemini_api_key')) {
                $table->dropColumn('gemini_api_key');
            }
        });

        Schema::table('generate_settings', function (Blueprint $table) {
            if (Schema::hasColumn('generate_settings', 'gemini_api_key')) {
                $table->dropColumn('gemini_api_key');
            }
        });
    }
};
