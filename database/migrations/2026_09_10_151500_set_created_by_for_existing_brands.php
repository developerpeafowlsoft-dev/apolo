<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set created_by for existing migrated/system brands to user 1 (Super Admin)
        DB::table('brands')->whereNull('created_by')->update(['created_by' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for backfill
    }
};
