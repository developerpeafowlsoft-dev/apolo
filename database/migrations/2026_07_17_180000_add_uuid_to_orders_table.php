<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'uuid')) {
                $table->string('uuid')->nullable()->unique()->after('id');
            }
        });

        // Populate UUIDs for existing orders
        try {
            $orders = \DB::table('orders')->whereNull('uuid')->get(['id']);
            foreach ($orders as $order) {
                \DB::table('orders')->where('id', $order->id)->update([
                    'uuid' => (string) Str::uuid()
                ]);
            }
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
