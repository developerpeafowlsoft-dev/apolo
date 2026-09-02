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
        Schema::table('generate_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('generate_settings', 'gstin')) {
                $table->string('gstin')->nullable();
            }
            if (!Schema::hasColumn('generate_settings', 'cin')) {
                $table->string('cin')->nullable();
            }
            if (!Schema::hasColumn('generate_settings', 'state_name')) {
                $table->string('state_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'cin', 'state_name']);
        });
    }
};
