<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('categories', 'show_in_hero')) {
                $table->boolean('show_in_hero')->default(0)->after('status')->index();
            }
        });

        // Set created_by for existing categories to user 1 (Super Admin)
        DB::table('categories')->whereNull('created_by')->update(['created_by' => 1]);

        // Default the 8 categories in Image 1 to show_in_hero = 1
        $initialHeroCategoryNames = [
            'FOOD',
            'SS CRADLE',
            'AUTOMATIC CRADLE KIT',
            'BABY NIPPLE',
            'FEDING BOTOL/SIPPER/OTHER ACCESSORIES/BREAST PUMP',
            'ADULT FOOD',
            'DEO',
            'DIPER ADULT'
        ];

        DB::table('categories')
            ->whereIn('name', $initialHeroCategoryNames)
            ->update(['show_in_hero' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('categories', 'show_in_hero')) {
                $table->dropColumn('show_in_hero');
            }
        });
    }
};
