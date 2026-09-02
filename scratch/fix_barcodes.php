<?php

use Illuminate\Support\Facades\DB;
use App\Models\ProductBarcode;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('order_products')->whereNull('barcode_number')->get()->each(function($op) {
    $usedBarcodes = DB::table('order_products')->whereNotNull('barcode_number')->pluck('barcode_number')->toArray();
    
    $bc = ProductBarcode::where('product_id', $op->product_id)
        ->where('is_sold', 1)
        ->whereNotIn('barcode_number', $usedBarcodes)
        ->first();
        
    if ($bc) {
        DB::table('order_products')
            ->where('order_id', $op->order_id)
            ->where('product_id', $op->product_id)
            ->whereNull('barcode_number')
            ->limit(1)
            ->update(['barcode_number' => $bc->barcode_number]);
    } else {
        $bc = ProductBarcode::where('product_id', $op->product_id)->first();
        if ($bc) {
            DB::table('order_products')
                ->where('order_id', $op->order_id)
                ->where('product_id', $op->product_id)
                ->whereNull('barcode_number')
                ->update(['barcode_number' => $bc->barcode_number]);
        }
    }
});

echo "Barcodes fixed successfully!\n";
