<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPurchaseRequest;
use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\FinancialYear;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPurchase;
use App\Models\VatTax;
use App\Repositories\InwardInvoiceRepository;
use App\Repositories\ProductPurchaseRepository;
use App\Services\Accounting\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $dayBooks = $shop?->counter()->get();
        $inwardTaxs = VatTax::active()->get(['id', 'name', 'percentage']);

        // Default Purchase List: show ONLY normal/non-Kachi entries (is_kachi = 0 or null)
        $inwardLists = ProductPurchase::with([
            'inwardInvoice:id,inward_voucher_no,inward_date,inward_challan_no,inward_challan_date,inward_party_code,inward_acc_gst_amount,inward_acc_net_amount,inward_acc_amt_with_gst,is_kachi',
            'inwardInvoice.partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no',
            'inwardInvoice.inwardProduct:id,inward_invoice_id,is_online_product'
        ])
            ->whereHas('inwardInvoice', function($q) {
                $q->where(function($subQ) {
                    $subQ->where('is_kachi', 0)->orWhereNull('is_kachi');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('shop.purchase-product.index',compact('dayBooks','inwardTaxs','inwardLists'));
    }


    public function edit($value)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $inwardDataIsPurchase = InwardInvoice::where(function ($query) use ($value) {

            $query->where('inward_voucher_no', $value)
                ->orWhere('inward_challan_no', $value);

        })->IsPurchase()->first();

        if (!empty($inwardDataIsPurchase)){
            return response()->json([
                'status' => false,
                'message' => __('already purchased cannot edited')
            ]);
        }

        $inwardData = InwardInvoice::with(['counter:id,code,counter_name','vattax:id,name,percentage','partyCode:id,accountName,accountshortcode,other_info_act_limit','inwardProduct','purchaser:id,name,last_name','season:id,name','agent:id,code,name','transport:id,code,name','deliveryBy:id,name','inwardProduct.products:id,name','inwardProduct.colors','inwardProduct.sizes','inwardProduct.designMaster:id,design_number','inwardProduct.hsnMaster:id,hsn_code','inwardProduct.vatTax:id,percentage'])

            ->where(function ($query) use ($value) {

                $query->where('inward_voucher_no', $value)
                    ->orWhere('inward_challan_no', $value);

            })->first();


        if (empty($inwardData)){
            return response()->json([
                'status' => false,
                'message' => __('No record found for this Voucher No. or Challan No.')
            ]);
        }

//        $inwardData->load([
//            'products:id,name',
//            'accountMasters:id,accountshortcode,accountName'
//        ]);
//        dd($inwardData);
        return response()->json([
            'inwardData' => $inwardData,
        ]);
    }

    public function store(ProductPurchaseRequest $request, VoucherService $voucherService)
    {

        $shop = generaleSetting('shop');

        $purchaseData = $request->purchaseData;

        $invoiceInward = InwardInvoice::where('id',$purchaseData['inward_invoice_id'])->where('inward_voucher_no',$purchaseData['inward_voucher_no'])->first();

        if (empty($invoiceInward)) {
            return response()->json([
                'status' => false,
                'message' => 'Inward Invoice not found.',
            ]);
        }

        try {

            DB::transaction(function () use ($purchaseData, $invoiceInward, $voucherService, $shop, $request) {

                // 1. Update InwardInvoice header with submitted purchaseData
                $invoiceInward->update([
                    'counter_master_id' => $purchaseData['counter_master_id'] ?? $invoiceInward->counter_master_id,
                    'inward_date' => $purchaseData['inward_date'] ?? $invoiceInward->inward_date,
                    'inward_day_name' => $purchaseData['inward_day_name'] ?? $invoiceInward->inward_day_name,
                    'inward_time' => $purchaseData['inward_time'] ?? $invoiceInward->inward_time,
                    'inward_challan_no' => $purchaseData['inward_challan_no'] ?? $invoiceInward->inward_challan_no,
                    'inward_challan_date' => $purchaseData['inward_challan_date'] ?? $invoiceInward->inward_challan_date,
                    'inward_party_code' => $purchaseData['inward_party_code'] ?? $invoiceInward->inward_party_code,
                    'inward_acc_purchaser' => $purchaseData['inward_acc_purchaser'] ?? $invoiceInward->inward_acc_purchaser,
                    'inward_acc_season' => $purchaseData['inward_acc_season'] ?? $invoiceInward->inward_acc_season,
                    'inward_acc_agent' => $purchaseData['inward_acc_agent'] ?? $invoiceInward->inward_acc_agent,
                    'inward_acc_transport' => $purchaseData['inward_acc_transport'] ?? $invoiceInward->inward_acc_transport,
                    'inward_acc_delivery_by' => $purchaseData['inward_acc_delivery_by'] ?? $invoiceInward->inward_acc_delivery_by,
                    'inward_acc_lr_no' => $purchaseData['inward_acc_lr_no'] ?? $invoiceInward->inward_acc_lr_no,
                    'inward_acc_lr_date' => $purchaseData['inward_acc_lr_date'] ?? $invoiceInward->inward_acc_lr_date,
                    'inward_acc_remark' => $purchaseData['inward_acc_remark'] ?? $invoiceInward->inward_acc_remark,
                    'inward_acc_gst_amount' => $purchaseData['inward_acc_gst_amount'] ?? $invoiceInward->inward_acc_gst_amount,
                    'inward_acc_net_amount' => $purchaseData['inward_acc_net_amount'] ?? $invoiceInward->inward_acc_net_amount,
                    'inward_acc_freight_amount' => $purchaseData['inward_acc_freight_amount'] ?? $invoiceInward->inward_acc_freight_amount,
                    'inward_acc_parcel_amount' => $purchaseData['inward_acc_parcel_amount'] ?? $invoiceInward->inward_acc_parcel_amount,
                    'inward_acc_amt_with_gst' => $purchaseData['inward_acc_amt_with_gst'] ?? $invoiceInward->inward_acc_amt_with_gst,
                ]);

                // 2. Loop through request rows and update InwardProduct records
                if (!empty($request->rows)) {
                    foreach ($request->rows as $row) {
                        if (!empty($row['inwardProductId'])) {
                            $inwardProd = InwardProduct::find($row['inwardProductId']);
                            if ($inwardProd) {
                                $inwardProd->update([
                                    'quantity' => $row['qty'],
                                    'buy_price' => $row['purcRate'],
                                    'price' => $row['amount'],
                                    'discount_price' => $row['disc'],
                                    'mrp' => $row['mrp'],
                                    'mark_up' => $row['mark_up'],
                                    'mark_down' => $row['mark_down'],
                                    'net_purc_rate' => $row['netPurcRate'],
                                    'hsn_master_id' => $row['taxCodeId'],
                                    'vat_tax_id' => $row['sgstId'],
                                ]);
                            }
                        }
                    }
                }

                $result = ProductPurchaseRepository::storeByProductPurchaseRequest(
                    $purchaseData,
                    $invoiceInward
                );

                $purchase  = $result['purchase'];
                $supplier  = $result['supplier'];
                $totTaxable = $result['totTaxable'];
                $totCgst   = $result['totCgst'];
                $totSgst   = $result['totSgst'];
                $totIgst   = $result['totIgst'];

                $freight = (float)($purchaseData['inward_acc_freight_amount'] ?? 0);
                $parcel  = (float)($purchaseData['inward_acc_parcel_amount'] ?? 0);
                $freightTotal = $freight + $parcel;

                // Re-calculate the grand total and round off with freight and parcel included
                $subtotal = $totTaxable + $totCgst + $totSgst + $totIgst + $freightTotal;
                $grandRounded = (float)round($subtotal, 0);
                $roundOff = (float)($grandRounded - $subtotal);
                $grand = $grandRounded;

                $isInterState = ($totIgst > 0);

                $purchaseAccountId = Account::whereIn('code', [$isInterState ? 'PUR_INTER' : 'PUR_LOCAL', 'PUR_LOCAL', 'PUR_INTER', 'PURCHASE'])->value('id')
                    ?? Account::where('account_group_id', 19)->value('id')
                    ?? Account::where('name', 'like', '%Purchase%')->value('id');

                $roundAccountId = Account::whereIn('code', ['EXP_ROF', 'ROUND', 'ROF'])->value('id')
                    ?? Account::where('name', 'like', '%Round%')->value('id');

                $cgstInputId = Account::whereIn('code', ['CGST_IN', 'GST_IN_C'])->value('id')
                    ?? Account::where('name', 'like', '%CGST Input%')->value('id');

                $sgstInputId = Account::whereIn('code', ['SGST_IN', 'GST_IN_S'])->value('id')
                    ?? Account::where('name', 'like', '%SGST Input%')->value('id');

                $igstInputId = Account::whereIn('code', ['IGST_IN', 'GST_IN_I'])->value('id')
                    ?? Account::where('name', 'like', '%IGST Input%')->value('id');

                $freightAccountId = Account::whereIn('code', ['EXP_FREIGHT', 'FRT_IN'])->value('id')
                    ?? Account::where('name', 'like', '%Freight%')->value('id');

                // ✅ 3. Build Voucher Entries
                $entries = [];

                // Purchase Account Debit
                if ($totTaxable > 0) {
                    $entries[] = [
                        'account_id' => $purchaseAccountId,
                        'type' => 'Dr',
                        'amount' => $totTaxable,
                        'description' => 'Purchase taxable'
                    ];
                }

                // CGST Input Debit
                if ($totCgst > 0) {
                    $entries[] = [
                        'account_id' => $cgstInputId,
                        'type' => 'Dr',
                        'amount' => $totCgst,
                        'description' => 'Input CGST'
                    ];
                }

                // SGST Input Debit
                if ($totSgst > 0) {
                    $entries[] = [
                        'account_id' => $sgstInputId,
                        'type' => 'Dr',
                        'amount' => $totSgst,
                        'description' => 'Input SGST'
                    ];
                }

                // IGST Input Debit
                if ($totIgst > 0) {
                    $entries[] = [
                        'account_id' => $igstInputId,
                        'type' => 'Dr',
                        'amount' => $totIgst,
                        'description' => 'Input IGST'
                    ];
                }

                // Freight Inward Debit
                if ($freightTotal > 0) {
                    $entries[] = [
                        'account_id' => $freightAccountId,
                        'type' => 'Dr',
                        'amount' => $freightTotal,
                        'description' => 'Freight Inward'
                    ];
                }

                // ✅ Round Off Entry: Positive → Dr, Negative → Cr
                if (abs($roundOff) >= 0.01) {
                    $entries[] = [
                        'account_id' => $roundAccountId,
                        'type' => $roundOff > 0 ? 'Dr' : 'Cr',
                        'amount' => abs($roundOff),
                        'description' => 'Round off'
                    ];
                }

                // ✅ Supplier Credit for Grand Total
                $entries[] = [
                    'account_id' => $supplier->account_id,
                    'type' => 'Cr',
                    'amount' => $grand,
                    'description' => 'Payable to supplier'
                ];

                $date = now();

                $financialYear = FinancialYear::where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('is_active', 1)
                    ->first();

                // ✅ 4. Create Voucher
                $voucher = $voucherService->create([
                    'voucher_type' => 'Purchase',
                    'date' => $purchase->bill_date ?? now(),
                    'narration' => $purchaseData['narration'] ?? ('Purchase from ' . ($supplier->accountName ?? 'Supplier')),
                    'shop_id' => $shop->id,
                    'financial_year_id' =>  $financialYear ? $financialYear->id : null,
//                    'seq_prefix' => $purchaseData['seq_prefix'] ?? null, // e.g. BR01-FY2526-PUR-
//                    'seq_padding' => $purchaseData['seq_padding'] ?? 6,
                    'entries' => $entries
                ]);

                // ✅ 5. Update Purchase with Voucher ID and Recalculated Values
                $purchase->update([
                    'total_taxable' => $totTaxable,
                    'total_cgst'    => $totCgst,
                    'total_sgst'    => $totSgst,
                    'total_igst'    => $totIgst,
                    'round_off'     => $roundOff,
                    'grand_total'   => $grand,
                    'voucher_id'    => $voucher->id
                ]);

                InwardInvoiceRepository::isPurchaseUpdate($invoiceInward);

                // Future tables
                // ProductPurchaseItemRepository::store(...);
                // StockRepository::store(...);
            });


            return response()->json([
                'status'  => true,
                'message' => __('Product Purchase created successfully!'),
            ]);

        } catch (\Exception $e) {

//             dd($e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong!',
                //'error' => $e->getMessage(), // development only
            ]);
        }

    }

//    public function purchaseProduct(Request $request)
//    {
//        $search = $request->search;
//
//        $shop = generaleSetting('shop');
//        $rootShop = generaleSetting('rootShop');
//
//
//        $inwardLists = InwardInvoice::select('id','shop_id','counter_master_id','inward_voucher_no','inward_date','inward_challan_no','inward_challan_date','inward_party_code','inward_acc_gst_amount','inward_acc_net_amount','inward_acc_amt_with_gst')->with(['counter:id,code,counter_name','partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no'])->when($search, function ($query) use ($search) {
//            return $query->where(function($q) use ($search) {
//                $q->where('inward_voucher_no', 'like', "%$search%")
//                    ->orWhere('inward_challan_no', 'like', "%$search%")
//                    ->orWhereHas('partyCode', function($partyQuery) use ($search) {
//                        $partyQuery->where('accountName', 'like', "%$search%");
//                    });
//            });
//        })->orderByDesc('id')->paginate(20)->withQueryString();
//
////        ->where('shop_id',$shop->id)
////dd($inwardLists);
//        if ($request->ajax()) {
//            return view('shop.inward-product.partials.inward-list', compact('inwardLists'))->render();
//        }
//    }

    public function purchaseProduct(Request $request)
    {
        $search = $request->search;
        $isKachi = $request->is_kachi;

        $inwardLists = ProductPurchase::with([
            'inwardInvoice:id,inward_voucher_no,inward_date,inward_challan_no,inward_challan_date,inward_party_code,inward_acc_gst_amount,inward_acc_net_amount,inward_acc_amt_with_gst,is_kachi',
            'inwardInvoice.partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no',
            'inwardInvoice.inwardProduct:id,inward_invoice_id,is_online_product'
        ])
            ->when($search, function ($query) use ($search) {

                $query->whereHas('inwardInvoice', function ($invoice) use ($search) {

                    $invoice->where('inward_voucher_no', 'like', "%{$search}%")
                        ->orWhere('inward_challan_no', 'like', "%{$search}%")
                        ->orWhereHas('partyCode', function ($party) use ($search) {
                            $party->where('accountName', 'like', "%{$search}%");
                        });

                });

            })
            ->when($isKachi !== null && $isKachi !== '', function($query) use ($isKachi) {
                if ($isKachi == 1 || $isKachi === '1' || $isKachi === true || $isKachi === 'true') {
                    return $query->whereHas('inwardInvoice', function($q) {
                        $q->where('is_kachi', 1);
                    });
                } else {
                    return $query->whereHas('inwardInvoice', function($q) {
                        $q->where(function($subQ) {
                            $subQ->where('is_kachi', 0)->orWhereNull('is_kachi');
                        });
                    });
                }
            }, function($query) {
                // Default: show ONLY normal/non-Kachi entries
                return $query->whereHas('inwardInvoice', function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('is_kachi', 0)->orWhereNull('is_kachi');
                    });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return view(
                'shop.purchase-product.partials.purchase-list',
                compact('inwardLists')
            )->render();
        }

        return view(
            'shop.purchase-product.partials.purchase-list',
            compact('inwardLists')
        );
    }

    public function view($id)
    {
        $purchase = ProductPurchase::with([

            'inwardInvoice:id,inward_voucher_no,inward_date,inward_challan_no,inward_challan_date,inward_party_code,inward_acc_gst_amount,inward_acc_net_amount,inward_acc_amt_with_gst,counter_master_id,vat_tax_id',

            'inwardInvoice.partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no,other_info_act_limit',

            'inwardInvoice.counter:id,code,counter_name',

            'inwardInvoice.vattax:id,name,percentage',

            'inwardInvoice.purchaser:id,name,last_name',

            'inwardInvoice.season:id,name',

            'inwardInvoice.agent:id,code,name',

            'inwardInvoice.transport:id,code,name',

            'inwardInvoice.deliveryBy:id,name',

            'inwardInvoice.inwardProduct',

            'inwardInvoice.inwardProduct.products:id,name',

            'inwardInvoice.inwardProduct.colors',

            'inwardInvoice.inwardProduct.sizes',

            'inwardInvoice.inwardProduct.designMaster:id,design_number',

            'inwardInvoice.inwardProduct.hsnMaster:id,hsn_code',

            'inwardInvoice.inwardProduct.vatTax:id,percentage',

        ])->findOrFail($id);



        return view(
            'shop.purchase-product.partials.view-purchase',
            compact('purchase')
        );
    }


//    public function onlineProductToggle($id)
//    {
////        dd($id);
//    }

//    public function onlineProductToggle($id, Request $request)
//    {
//        try {
//            // 1. Find the product purchase
//            $productPurchase = ProductPurchase::with('inwardInvoice')->find($id);
//
//            if (!$productPurchase) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'Product purchase not found!'
//                ]);
//            }
//
//            $inwardInvoiceId = $productPurchase->inward_invoice_id;
//
//            // 2. Get all inward products for this invoice
//            $inwardProducts = InwardProduct::where('inward_invoice_id', $inwardInvoiceId)
//                ->with(['products', 'designMaster'])
//                ->get();
//
//            if ($inwardProducts->isEmpty()) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'No products found in this purchase!'
//                ]);
//            }
//
//            DB::beginTransaction();
//
//            $productsInserted = [];
//
//            // 3. Process each inward product - ALWAYS CREATE NEW
//            foreach ($inwardProducts as $inwardProduct) {
//                // ✅ ALWAYS create new product (Don't check for existing)
//                $product = $this->createNewProduct($inwardProduct);
//                $productsInserted[] = $product->id;
//
//                \Log::info('New product created from inward product:', [
//                    'inward_product_id' => $inwardProduct->id,
//                    'new_product_id' => $product->id,
//                    'name' => $product->name,
//                    'price' => $product->price,
//                    'quantity' => $product->quantity
//                ]);
//            }
//
//            // 4. Update product purchase status
//            $productPurchase->is_online_product = true;
//            $productPurchase->save();
//
//            DB::commit();
//
//            return response()->json([
//                'status' => true,
//                'message' => count($productsInserted) . ' products have been added to online store!',
//                'products_count' => count($productsInserted)
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            \Log::error('Error in onlineProductToggle:', [
//                'message' => $e->getMessage(),
//                'line' => $e->getLine()
//            ]);
//
//            return response()->json([
//                'status' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ]);
//        }
//    }

    /**
     * Create NEW product from inward product - ALWAYS creates fresh entry
     */
//    private function createNewProduct($inwardProduct)
//    {
//        $designMaster = $inwardProduct->designMaster;
//        $sourceProduct = $inwardProduct->products; // ✅ Relation name 'products'
//
//        // ✅ Generate unique code based on source product
////        $code = $this->generateUniqueCode($sourceProduct->code ?? $designMaster->design_number ?? 'PROD');
//
//        // ✅ Create BRAND NEW product
//        $product = new Product();
//        $product->shop_id = $inwardProduct->shop_id ?? generaleSetting('shop')?->id;
//
//        // ✅ Name: Source product name + Vendor/Design info (to differentiate)
//        $vendorName = $designMaster->account_master_name ?? 'Vendor';
//        $product->name = $sourceProduct->name ?? $designMaster->Item_name ?? 'Product';
//
//        // ✅ CODE: Unique code generated
////        $product->code = $code;
//
//        $product->brand_id = $sourceProduct->brand_id ?? null;
//        $product->unit_id = $sourceProduct->unit_id ?? null;
//
//        // ✅ Use inward product prices (vendor specific)
//        $product->price = $inwardProduct->price ?? 0;
//        $product->buy_price = $inwardProduct->buy_price ?? 0;
//        $product->discount_price = $inwardProduct->discount_price ?? 0;
//        $product->quantity = $inwardProduct->quantity ?? 0;
//        $product->mrp = $inwardProduct->mrp ?? 0;
//
//        $product->min_order_quantity = 1;
//
//        // ✅ Direct active karein (vendor products should be active)
//        $product->is_active = true;
//        $product->is_approve = true;
//        $product->is_online_product = true;
//        $product->is_publish_online = true;
//        $product->is_item_master = 0; // ✅ 0 = Online Product Entry (vendor product)
//
//        $product->hsn_master_id = $inwardProduct->hsn_master_id ?? null;
//        $product->vat_tax_id = $inwardProduct->vat_tax_id ?? null;
//        $product->description = $sourceProduct->description ?? $designMaster->remark ?? null;
//        $product->short_description = $sourceProduct->short_description ?? null;
//        $product->save();
//
//        \Log::info('New product created:', [
//            'id' => $product->id,
//            'name' => $product->name,
//            'code' => $product->code,
//            'price' => $product->price,
//            'quantity' => $product->quantity,
//            'is_item_master' => $product->is_item_master
//        ]);
//
//        // ✅ Attach colors from inward product
//        $this->syncProductColors($product, $inwardProduct);
//
//        // ✅ Attach sizes from inward product
//        $this->syncProductSizes($product, $inwardProduct);
//
//        // ✅ Copy categories from source product
//        if ($sourceProduct && $sourceProduct->categories) {
//            $product->categories()->sync($sourceProduct->categories->pluck('id')->toArray());
//        }
//
//        // ✅ Copy media if exists
//        if ($sourceProduct && $sourceProduct->media_id) {
//            $product->media_id = $sourceProduct->media_id;
//            $product->save();
//        }
//
//        return $product;
//    }

    /**
     * Generate unique product code
     */
//    private function generateUniqueCode($baseCode)
//    {
//        // Clean the base code
//        $code = strtoupper(Str::slug($baseCode, ''));
//        $code = preg_replace('/[^A-Z0-9]/', '', $code);
//
//        if (strlen($code) < 4) {
//            $code = 'PROD' . rand(1000, 9999);
//        }
//
//        // ✅ Add timestamp to ensure uniqueness
//        $code = $code . date('ymd') . rand(100, 999);
//
//        // Final check for uniqueness
//        $existing = Product::where('code', $code)->first();
//        $counter = 1;
//        while ($existing) {
//            $code = $code . rand(10, 99);
//            $existing = Product::where('code', $code)->first();
//            $counter++;
//            if ($counter > 5) break;
//        }
//
//        return $code;
//    }

    /**
     * Sync product colors from inward product
     */
//    private function syncProductColors($product, $inwardProduct)
//    {
//        $colors = DB::table('inward_product_colors')
//            ->where('inward_product_id', $inwardProduct->id)
//            ->get();
//
//        if ($colors->isNotEmpty()) {
//            $colorData = [];
//            foreach ($colors as $color) {
//                $colorData[$color->color_id] = ['price' => $color->price ?? 0];
//            }
//            $product->colors()->sync($colorData);
//
//            \Log::info('Colors synced:', [
//                'product_id' => $product->id,
//                'colors' => $colorData
//            ]);
//        }
//    }

    /**
     * Sync product sizes from inward product
     */
//    private function syncProductSizes($product, $inwardProduct)
//    {
//        $sizes = DB::table('inward_product_sizes')
//            ->where('inward_product_id', $inwardProduct->id)
//            ->get();
//
//        if ($sizes->isNotEmpty()) {
//            $sizeData = [];
//            foreach ($sizes as $size) {
//                $sizeData[$size->size_id] = ['price' => $size->price ?? 0];
//            }
//            $product->sizes()->sync($sizeData);
//
//            \Log::info('Sizes synced:', [
//                'product_id' => $product->id,
//                'sizes' => $sizeData
//            ]);
//        }
//    }

    /**
     * Generate unique product code
     */
//    private function generateUniqueCode($baseCode)
//    {
//        $code = strtoupper(Str::slug($baseCode, ''));
//        $code = preg_replace('/[^A-Z0-9]/', '', $code);
//
//        // If code is empty or too short, generate from product name
//        if (strlen($code) < 4) {
//            $code = 'PROD' . rand(1000, 9999);
//        }
//
//        // Check if code exists
//        $existing = Product::where('code', $code)->first();
//        if ($existing) {
//            $code = $code . rand(100, 999);
//        }
//
//        return $code;
//    }

    public function onlineProductToggle($id, Request $request)
    {
        try {
            $productPurchase = ProductPurchase::with('inwardInvoice')->find($id);

            if (!$productPurchase) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product purchase not found!'
                ]);
            }

            $targetState = !$productPurchase->is_online_product;

            $inwardInvoiceId = $productPurchase->inward_invoice_id;

            $inwardProducts = InwardProduct::where('inward_invoice_id', $inwardInvoiceId)
                ->with(['products', 'designMaster'])
                ->get();

            if ($inwardProducts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found in this purchase!'
                ]);
            }

            DB::beginTransaction();

            $productsProcessed = [];

            if ($targetState) {
                // TOGGLE ON: Make available on online shop
                foreach ($inwardProducts as $inwardProduct) {
                    $designMasterId = $inwardProduct->design_master_id;

                    $existingProduct = Product::where('design_master_id', $designMasterId)
                        ->where('is_item_master', 0)
                        ->first();

                    if ($existingProduct) {
                        $product = $this->updateExistingProduct($existingProduct, $inwardProduct);
                    } else {
                        $product = $this->createNewProduct($inwardProduct);
                    }

                    $product->is_online_product = true;
                    $product->is_update_product = true;
                    $product->is_active = true;
                    $product->save();

                    $productsProcessed[] = $product->id;
                }

                $productPurchase->is_online_product = true;
                $productPurchase->save();

                $message = __('Products are now available on the online shop.');
            } else {
                // TOGGLE OFF: Remove from online shop
                foreach ($inwardProducts as $inwardProduct) {
                    $designMasterId = $inwardProduct->design_master_id;

                    $existingProduct = Product::where('design_master_id', $designMasterId)
                        ->where('is_item_master', 0)
                        ->first();

                    if ($existingProduct) {
                        $existingProduct->is_online_product = false;
                        $existingProduct->save();
                        $productsProcessed[] = $existingProduct->id;
                    }
                }

                $productPurchase->is_online_product = false;
                $productPurchase->save();

                $message = __('Products have been removed from the online shop.');
            }

            DB::commit();

            try {
                if (function_exists('cache') && cache()->supportsTags()) {
                    cache()->tags(['products', 'shop_products'])->flush();
                } else {
                    \Illuminate\Support\Facades\Cache::forget('online_products');
                }
            } catch (\Exception $cEx) {
                // Ignore cache exceptions
            }

            return response()->json([
                'status' => true,
                'is_online' => (bool)$productPurchase->is_online_product,
                'message' => $message,
                'products_count' => count($productsProcessed)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in onlineProductToggle:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * TOGGLE single item online availability from purchase details modal
     */
    public function toggleSingleItemOnline($inwardProductId, Request $request)
    {
        try {
            $inwardProduct = InwardProduct::with(['products', 'designMaster'])->find($inwardProductId);

            if (!$inwardProduct) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item not found in purchase records!'
                ]);
            }

            $designMasterId = $inwardProduct->design_master_id;

            // Check if online product already exists for this design master
            $existingProduct = Product::where('design_master_id', $designMasterId)
                ->where('is_item_master', 0)
                ->first();

            // Check if barcode is generated for THIS SPECIFIC inward product variant
            $hasBarcode = ProductBarcode::where('inward_product_id', $inwardProductId)->exists();

            $isCurrentlyOnline = (bool)($inwardProduct->is_online_product ?? false);

            DB::beginTransaction();

            if ($isCurrentlyOnline) {
                // Currently ON ➔ TOGGLE OFF for this specific variant
                $inwardProduct->is_online_product = false;
                $inwardProduct->save();

                // Check if any inward variant of this design master is still online
                $anyOnlineLeft = InwardProduct::where('design_master_id', $designMasterId)
                    ->where('is_online_product', 1)
                    ->exists();

                if ($existingProduct && !$anyOnlineLeft) {
                    $existingProduct->is_online_product = false;
                    $existingProduct->save();
                }

                $newStatus = false;
                $message = __('Item removed from online shop.');
            } else {
                // Currently OFF ➔ TOGGLE ON: Check if barcode is generated for this specific variant
                if (!$hasBarcode) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => __('Please generate the barcode for this product variant before enabling Sell Online.')
                    ]);
                }

                $inwardProduct->is_online_product = true;
                $inwardProduct->save();

                if ($existingProduct) {
                    $product = $this->updateExistingProduct($existingProduct, $inwardProduct);
                } else {
                    $product = $this->createNewProduct($inwardProduct);
                }

                $product->is_online_product = true;
                $product->is_update_product = true;
                $product->is_active = true;
                $product->save();
                $newStatus = true;
                $message = __('Item is now available on the online shop.');
            }

            // Sync parent purchase order's online status indicator
            $inwardInvoiceId = $inwardProduct->inward_invoice_id;
            $productPurchase = ProductPurchase::where('inward_invoice_id', $inwardInvoiceId)->first();

            if ($productPurchase) {
                $hasOnlineItem = InwardProduct::where('inward_invoice_id', $inwardInvoiceId)
                    ->where('is_online_product', 1)
                    ->exists();

                $productPurchase->is_online_product = $hasOnlineItem;
                $productPurchase->save();
            }

            $totalCount = InwardProduct::where('inward_invoice_id', $inwardInvoiceId)->count();
            $onlineCount = InwardProduct::where('inward_invoice_id', $inwardInvoiceId)->where('is_online_product', 1)->count();

            DB::commit();

            try {
                if (function_exists('cache') && cache()->supportsTags()) {
                    cache()->tags(['products', 'shop_products'])->flush();
                } else {
                    \Illuminate\Support\Facades\Cache::forget('online_products');
                }
            } catch (\Exception $cEx) {
                // Ignore cache exceptions
            }

            return response()->json([
                'status' => true,
                'is_online' => $newStatus,
                'message' => $message,
                'inward_product_id' => $inwardProductId,
                'purchase_id' => $productPurchase?->id,
                'online_count' => $onlineCount,
                'total_count' => $totalCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in toggleSingleItemOnline:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * CREATE NEW product (when design master doesn't exist in products)
     */
    private function createNewProduct($inwardProduct)
    {
        $designMaster = $inwardProduct->designMaster;
        $sourceProduct = $inwardProduct->products;

        $product = new Product();
        $product->shop_id = $inwardProduct->shop_id ?? generaleSetting('shop')?->id;
        $product->name = $sourceProduct->name ?? $designMaster->Item_name ?? 'Product';
//        $product->code = $this->generateUniqueCode($sourceProduct->code ?? $designMaster->design_number ?? 'PROD');
        $product->design_master_id = $designMaster->id; // ✅ Store design master ID
//        $product->inward_invoice_id = $inwardProduct->inward_invoice_id;
        $product->inward_invoice_ids = json_encode([$inwardProduct->inward_invoice_id]);

        $product->brand_id = $sourceProduct->brand_id ?? null;
        $product->unit_id = $sourceProduct->unit_id ?? null;
        $product->price = $inwardProduct->price ?? 0;
        $product->buy_price = $inwardProduct->buy_price ?? 0;
        $product->discount_price = $inwardProduct->discount_price ?? 0;
        $product->quantity = $inwardProduct->quantity ?? 0;
        $product->mrp = $inwardProduct->mrp ?? 0;
        $product->min_order_quantity = 1;
        $product->is_active = true;
        $product->is_approve = false;
        $product->is_online_product = true;
        $product->is_update_product = true;
        $product->is_publish_online = false;
        $product->is_item_master = 0;
        $product->hsn_master_id = $inwardProduct->hsn_master_id ?? null;
        $product->vat_tax_id = $inwardProduct->vat_tax_id ?? null;
        $product->description = $sourceProduct->description ?? $designMaster->remark ?? null;
        $product->short_description = $sourceProduct->short_description ?? null;
        $product->save();

        // Sync colors, sizes, categories
        $this->syncProductColors($product, $inwardProduct);
        $this->syncProductSizes($product, $inwardProduct);

        if ($sourceProduct && $sourceProduct->categories) {
            $product->categories()->sync($sourceProduct->categories->pluck('id')->toArray());
        }

        if ($sourceProduct && $sourceProduct->media_id) {
            $product->media_id = $sourceProduct->media_id;
            $product->save();
        }

        return $product;
    }

    /**
     * UPDATE existing product (when same design master exists)
     */
    private function updateExistingProduct($existingProduct, $inwardProduct)
    {
        // ✅ Update prices (use latest)
        $existingProduct->price = $inwardProduct->price ?? $existingProduct->price;
        $existingProduct->buy_price = $inwardProduct->buy_price ?? $existingProduct->buy_price;
        $existingProduct->mrp = $inwardProduct->mrp ?? $existingProduct->mrp;
        $existingProduct->discount_price = $inwardProduct->discount_price ?? $existingProduct->discount_price;

        // ✅ Add quantity (don't replace)
        $existingProduct->quantity += $inwardProduct->quantity ?? 0;

        $existingInvoiceIds = $existingProduct->inward_invoice_ids;

        // Agar string hai toh decode karein
        if (is_string($existingInvoiceIds)) {
            $existingInvoiceIds = json_decode($existingInvoiceIds, true) ?? [];
        }

        // Agar array nahi hai toh empty array
        if (!is_array($existingInvoiceIds)) {
            $existingInvoiceIds = [];
        }

        // ✅ Add new invoice ID
        if (!in_array($inwardProduct->inward_invoice_id, $existingInvoiceIds)) {
            $existingInvoiceIds[] = $inwardProduct->inward_invoice_id;
            $existingProduct->inward_invoice_ids = json_encode($existingInvoiceIds); // ✅ JSON string store
        }

        // ✅ Mark as online
        $existingProduct->is_online_product = true;
        $existingProduct->is_update_product = true;
        $existingProduct->is_publish_online = false;
        $existingProduct->is_active = true;
        $existingProduct->is_approve = false;

        $existingProduct->save();

        // ✅ Sync colors (add new colors, keep existing)
        $this->syncProductColors($existingProduct, $inwardProduct);

        // ✅ Sync sizes (add new sizes, keep existing)
        $this->syncProductSizes($existingProduct, $inwardProduct);

        return $existingProduct;
    }

    /**
     * Sync colors - ADD new colors without removing existing
     */
    private function syncProductColors($product, $inwardProduct)
    {
        $colors = DB::table('inward_product_colors')
            ->where('inward_product_id', $inwardProduct->id)
            ->get();

        if ($colors->isNotEmpty()) {
            foreach ($colors as $color) {
                // ✅ Check if color already exists
                $exists = DB::table('product_colors')
                    ->where('product_id', $product->id)
                    ->where('color_id', $color->color_id)
                    ->exists();

                if (!$exists) {
                    // ✅ Only attach if not exists
                    $product->colors()->attach($color->color_id, ['price' => $color->price ?? 0]);
                    \Log::info('Color added:', [
                        'product_id' => $product->id,
                        'color_id' => $color->color_id
                    ]);
                } else {
                    // ✅ Update price if exists
                    DB::table('product_colors')
                        ->where('product_id', $product->id)
                        ->where('color_id', $color->color_id)
                        ->update(['price' => $color->price ?? 0]);
                }
            }
        }
    }

    /**
     * Sync sizes - ADD new sizes without removing existing
     */
    private function syncProductSizes($product, $inwardProduct)
    {
        $sizes = DB::table('inward_product_sizes')
            ->where('inward_product_id', $inwardProduct->id)
            ->get();

        if ($sizes->isNotEmpty()) {
            foreach ($sizes as $size) {
                // ✅ Check if size already exists
                $exists = DB::table('product_sizes')
                    ->where('product_id', $product->id)
                    ->where('size_id', $size->size_id)
                    ->exists();

                if (!$exists) {
                    // ✅ Only attach if not exists
                    $product->sizes()->attach($size->size_id, ['price' => $size->price ?? 0]);
                    \Log::info('Size added:', [
                        'product_id' => $product->id,
                        'size_id' => $size->size_id
                    ]);
                } else {
                    // ✅ Update price if exists
                    DB::table('product_sizes')
                        ->where('product_id', $product->id)
                        ->where('size_id', $size->size_id)
                        ->update(['price' => $size->price ?? 0]);
                }
            }
        }
    }

    /**
     * Generate unique product code
     */
//    private function generateUniqueCode($baseCode)
//    {
//        $code = strtoupper(Str::slug($baseCode, ''));
//        $code = preg_replace('/[^A-Z0-9]/', '', $code);
//
//        if (strlen($code) < 4) {
//            $code = 'PROD' . rand(1000, 9999);
//        }
//
//        $code = $code . date('ymd') . rand(100, 999);
//
//        $existing = Product::where('code', $code)->first();
//        while ($existing) {
//            $code = $code . rand(10, 99);
//            $existing = Product::where('code', $code)->first();
//        }
//
//        return $code;
//    }
}
