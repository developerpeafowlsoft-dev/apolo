<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Salesman;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->float('mrp')->nullable()->after('video_id');
            $table->decimal('mark_up', 10, 2)->default(0.00);
            $table->decimal('mark_down', 10, 2)->default(0.00);
            $table->foreignIdFor(Salesman::class)->nullable()->constrained()->onDelete('set null');
            $table->string('commission_type',7)->nullable()->comment('1 = comm% 2 = comm₹');
            $table->decimal('salesman_comm', 10, 2)->default(0.00);
            $table->decimal('salesman_comm_amt', 10, 2)->default(0.00);
            $table->boolean('is_online_product')->default(false);
            $table->boolean('is_publish_online')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
