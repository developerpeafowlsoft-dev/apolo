<?php

namespace App\Http\Controllers\API\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Voucher;
use App\Models\ProductPurchase;
use App\Models\Product;
use App\Models\StockReconciliationFlag;
use App\Services\Accounting\VoucherService;
use Exception;

class SyncIngestController extends Controller
{
    /**
     * Store a received synchronization payload.
     */
    public function store(Request $request)
    {
        $branch = $request->attributes->get('active_branch');

        $validated = $request->validate([
            'syncable_type' => 'required|string',
            'syncable_id' => 'required|integer',
            'payload' => 'required|array',
        ]);

        $type = $validated['syncable_type'];
        $originalId = $validated['syncable_id'];
        $payload = $validated['payload'];

        if (!class_exists($type)) {
            return response()->json([
                'success' => false,
                'error' => "Invalid syncable model class: {$type}"
            ], 400);
        }

        // Idempotency check: branch_id + original_id
        $duplicate = $type::where('branch_id', $branch->id)
            ->where('original_id', $originalId)
            ->exists();

        if ($duplicate) {
            info("Duplicate sync request received from branch {$branch->branch_code} for {$type} original ID {$originalId}");
            return response()->json([
                'success' => true,
                'message' => 'Record already synced (duplicate skipped)'
            ], 200);
        }

        try {
            DB::transaction(function () use ($branch, $type, $originalId, $payload) {
                if ($type === Voucher::class) {
                    $voucherService = app(VoucherService::class);
                    
                    $entries = $payload['entries'] ?? [];
                    
                    $voucherData = array_merge($payload, [
                        'branch_id' => $branch->id,
                        'original_id' => $originalId,
                        'entries' => $entries,
                    ]);

                    $voucherService->create($voucherData);
                } 
                elseif ($type === Order::class) {
                    $orderData = array_merge($payload, [
                        'branch_id' => $branch->id,
                        'original_id' => $originalId,
                    ]);
                    
                    $attributes = collect($orderData)->except(['products', 'payments', 'customer', 'branch', 'id'])->toArray();
                    $order = Order::create($attributes);

                    // Reconcile central product stock
                    $products = $payload['products'] ?? [];
                    foreach ($products as $p) {
                        $productId = $p['id'] ?? null;
                        $qty = $p['pivot']['quantity'] ?? 0;

                        if ($productId && $qty > 0) {
                            $product = Product::withoutGlobalScopes()->find($productId);
                            if ($product) {
                                $prevStock = $product->quantity;
                                $newStock = $prevStock - $qty;

                                if ($newStock < 0) {
                                    StockReconciliationFlag::create([
                                        'branch_id' => $branch->id,
                                        'product_id' => $product->id,
                                        'reported_sale_qty' => $qty,
                                        'previous_stock' => $prevStock,
                                        'new_stock' => $newStock,
                                        'reason' => "Sale of {$qty} units pushed central stock negative (previous: {$prevStock}).",
                                    ]);
                                    $product->quantity = 0;
                                } else {
                                    $product->quantity = $newStock;
                                }
                                $product->save();
                            }
                        }
                    }
                } 
                elseif ($type === ProductPurchase::class) {
                    $purchaseData = array_merge($payload, [
                        'branch_id' => $branch->id,
                        'original_id' => $originalId,
                    ]);
                    $attributes = collect($purchaseData)->except(['inward_invoice', 'branch', 'id'])->toArray();
                    ProductPurchase::create($attributes);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Sync ingestion successful.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
