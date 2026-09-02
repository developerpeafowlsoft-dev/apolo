<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counter_masters', function (Blueprint $table) {
            $table->string('floor', 100)->nullable()->after('counter_short_name');
        });
    }

    public function down(): void
    {
        Schema::table('counter_masters', function (Blueprint $table) {
            $table->dropColumn('floor');
        });
    }
};
