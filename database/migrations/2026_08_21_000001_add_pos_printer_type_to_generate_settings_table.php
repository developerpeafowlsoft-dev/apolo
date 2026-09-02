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
        if (!Schema::hasColumn('generate_settings', 'pos_printer_type')) {
            Schema::table('generate_settings', function (Blueprint $table) {
                $table->string('pos_printer_type')->default('thermal')->after('is_e_invoice');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('generate_settings', 'pos_printer_type')) {
            Schema::table('generate_settings', function (Blueprint $table) {
                $table->dropColumn('pos_printer_type');
            });
        }
    }
};
