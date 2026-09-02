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
        if (! Schema::hasColumn('generate_settings', 'shiprocket_pickup_pincode')) {
            Schema::table('generate_settings', function (Blueprint $table) {
                $table->string('shiprocket_pickup_pincode')->nullable()->default('384170')->after('state_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('generate_settings', 'shiprocket_pickup_pincode')) {
            Schema::table('generate_settings', function (Blueprint $table) {
                $table->dropColumn('shiprocket_pickup_pincode');
            });
        }
    }
};
