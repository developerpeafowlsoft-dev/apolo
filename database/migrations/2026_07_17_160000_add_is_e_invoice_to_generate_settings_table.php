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
        if (!Schema::hasColumn('generate_settings', 'is_e_invoice')) {
            Schema::table('generate_settings', function (Blueprint $table) {
                $table->boolean('is_e_invoice')->default(false);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'e_invoice_irn')) {
                $table->string('e_invoice_irn')->nullable();
            }
            if (!Schema::hasColumn('orders', 'e_invoice_ack_no')) {
                $table->string('e_invoice_ack_no')->nullable();
            }
            if (!Schema::hasColumn('orders', 'e_invoice_ack_date')) {
                $table->timestamp('e_invoice_ack_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->dropColumn('is_e_invoice');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['e_invoice_irn', 'e_invoice_ack_no', 'e_invoice_ack_date']);
        });
    }
};
