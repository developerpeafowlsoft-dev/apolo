<?php

namespace App\Http\Controllers\Shop;

use App\Events\AdminProductRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\InwardProductRequest;
use App\Models\DesignMaster;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use App\Models\MasterPrice;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\VatTax;
use App\Repositories\DesignMasterRepository;
use App\Repositories\InwardInvoiceRepository;
use App\Repositories\InwardProductRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class InwardProductController extends Controller
{
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $dayBooks = $shop?->counter()->get();
        $inwardTaxs = VatTax::active()->get(['id', 'name', 'percentage']);

        // Default Inward List: show ONLY normal/final inward entries (is_kachi = 0 or null)
        $inwardLists = InwardInvoice::select('id','shop_id','counter_master_id','inward_voucher_no','inward_date','inward_challan_no','inward_challan_date','inward_party_code','inward_acc_gst_amount','inward_acc_net_amount','inward_acc_amt_with_gst','is_kachi')
            ->with(['counter:id,code,counter_name','partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no','inwardProduct','inwardProduct.products:id,name','inwardProduct.designMaster:id,design_number','inwardProduct.vatTax:id,name,percentage','inwardProduct.hsnMaster:id,hsn_code'])
            ->where(function($q) {
                $q->where('is_kachi', 0)->orWhereNull('is_kachi');
            })
            ->where('shop_id',$shop->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.inward-product.index',compact('dayBooks','inwardTaxs','inwardLists'));
    }


    public function designDataGet(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');


        $itemSearch = $request->itemSearch ?? '';
        $colorSearch = $request->colorSearch ?? '';
        $sizeSearch = $request->sizeSearch ?? '';
        $taxCodeSearch = $request->taxCodeSearch ?? '';
        $designNoSearch = $request->designNoSearch ?? '';


        if (!empty($itemSearch) || !empty($designNoSearch)){
            $designWithItemData = $shop?->designMasters()->active()
                ->when($itemSearch, function ($query) use ($itemSearch) {
                    $query->whereHas('products', function($q) use ($itemSearch) {
                        $q->where('name', 'like', "%$itemSearch%");
                    });
                })
                ->when($designNoSearch, function ($query) use ($designNoSearch) {
                    $query->where('design_number', 'like', "%{$designNoSearch}%");
                })
                ->with(['products:id,name,hsn_master_id,vat_tax_id','products.hsnMaster:id,hsn_code','products.vatTax:id,percentage'])
                ->get();

            $suggestions = collect();

            // Add existing designs to suggestions
            foreach ($designWithItemData as $design) {
                $suggestions->push([
                    'id' => $design->id,
                    'design_number' => $design->design_number,
                    'buy_price' => $design->buy_price,
                    'mrp' => $design->mrp,
                    'mark_up' => $design->mark_up,
                    'mark_down' => $design->mark_down,
                    'discount_percentage' => $design->discount_percentage,
                    'price' => $design->price,
                    'quantity' => $design->quantity,
                    'products' => $design->products
                ]);
            }

            // Search product master directly for matching products
            if ($itemSearch) {
                $matchingProducts = Product::where('shop_id', $shop->id)
                    ->where('name', 'like', "%$itemSearch%")
                    ->with(['hsnMaster:id,hsn_code', 'vatTax:id,percentage'])
                    ->get();

                foreach ($matchingProducts as $prod) {
                    $suggestions->push([
                        'id' => '',
                        'design_number' => '',
                        'buy_price' => '',
                        'mrp' => '',
                        'mark_up' => '',
                        'mark_down' => '',
                        'discount_percentage' => '',
                        'price' => '',
                        'quantity' => '',
                        'products' => [
                            'id' => $prod->id,
                            'name' => $prod->name,
                            'hsn_master_id' => $prod->hsn_master_id,
                            'vat_tax_id' => $prod->vat_tax_id,
                            'hsn_master' => $prod->hsnMaster,
                            'vat_tax' => $prod->vatTax
                        ]
                    ]);
                }
            }

            return response()->json([
                'designWithItemData' => $suggestions,
                'status' => true
            ]);
        }elseif (!empty($colorSearch)){
//            dd("Hello",$request->colorSearch);
            if ($request->colorSearch){
                $designWithColorData = $rootShop?->colors()->Isactive()
                    ->where('name', 'like', '%' . $colorSearch . '%')
                    ->get(['id','name']);

                if (!empty($designWithColorData)){
                    return response()->json([
                        'designWithColorData' => $designWithColorData,
                        'status' => true
                    ]);
                }
            }

        }elseif (!empty($sizeSearch)){
            if ($request->sizeSearch){
                $designWithSizeData = $rootShop?->sizes()->Isactive()
                    ->where('name', 'like', '%' . $sizeSearch . '%')
                    ->get(['id','name']);

                if (!empty($designWithSizeData)){
                    return response()->json([
                        'designWithSizeData' => $designWithSizeData,
                        'status' => true
                    ]);
                }
            }
        }elseif (!empty($taxCodeSearch)){
            if ($request->taxCodeSearch){
                $designWithtaxCodeData = $shop?->hsnmasters()->Isactive()
                    ->with('vattax:id,percentage')
                    ->where('hsn_code', 'like', '%' . $taxCodeSearch . '%')
                    ->get(['id','hsn_code','vat_tax_id']);
//dd($taxCodeSearch,$designWithtaxCodeData);
                if (!empty($designWithtaxCodeData)){
                    return response()->json([
                        'designWithtaxCodeData' => $designWithtaxCodeData,
                        'status' => true
                    ]);
                }
            }
        }
        return response()->json([
            'message' => 'No data found',
            'status' => false
        ]);
    }

    public function getNextKachiSequence(Request $request)
    {
        $shop = generaleSetting('shop');
        $shopId = $shop?->id;

        $lastKachi = InwardInvoice::where('shop_id', $shopId)
            ->where('is_kachi', 1)
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastKachi && !empty($lastKachi->inward_voucher_no)) {
            $numericVal = (int) preg_replace('/\D/', '', $lastKachi->inward_voucher_no);
            if ($numericVal > 0) {
                $nextNum = $numericVal + 1;
            }
        }

        $formatted = sprintf('%06d', $nextNum);

        return response()->json([
            'status' => true,
            'sequence' => $formatted
        ]);
    }

    public function store(InwardProductRequest $request)
    {
        $shop = generaleSetting('shop');


        if (!empty($request->rows)) {
            $invoiceHeaderData = $request->invoiceData;
            $rows = $request->rows;
            $isKachi = !empty($invoiceHeaderData['is_kachi']) ? true : false;

            try {
                $inwardData = null;

                DB::transaction(function() use ($request, &$invoiceHeaderData, &$inwardData, $rows, $isKachi, $shop) {
                    if ($isKachi) {
                        // Safety & Concurrency check for Kachi Entry sequence numbers
                        $clientSeq = trim($invoiceHeaderData['inward_voucher_no'] ?? '');

                        $existing = null;
                        if (!empty($clientSeq)) {
                            $existing = InwardInvoice::where('shop_id', $shop->id)
                                ->where('is_kachi', 1)
                                ->where('inward_voucher_no', $clientSeq)
                                ->lockForUpdate()
                                ->first();
                        }

                        if ($existing || empty($clientSeq)) {
                            $lastKachi = InwardInvoice::where('shop_id', $shop->id)
                                ->where('is_kachi', 1)
                                ->orderBy('id', 'desc')
                                ->lockForUpdate()
                                ->first();

                            $nextNum = 1;
                            if ($lastKachi && !empty($lastKachi->inward_voucher_no)) {
                                $numericVal = (int) preg_replace('/\D/', '', $lastKachi->inward_voucher_no);
                                if ($numericVal > 0) {
                                    $nextNum = $numericVal + 1;
                                }
                            }
                            $seq = sprintf('%06d', $nextNum);
                            $invoiceHeaderData['inward_voucher_no'] = $seq;
                            $invoiceHeaderData['inward_challan_no'] = $seq;
                        } else {
                            // Ensure 6-digit zero padding format
                            $numericVal = (int) preg_replace('/\D/', '', $clientSeq);
                            if ($numericVal > 0) {
                                $seq = sprintf('%06d', $numericVal);
                                $invoiceHeaderData['inward_voucher_no'] = $seq;
                                $invoiceHeaderData['inward_challan_no'] = $seq;
                            }
                        }
                    }

                    // Inward Invoice Save
                    $inwardData = InwardInvoiceRepository::storeByInwardInvoiceRequest($invoiceHeaderData);

                    foreach ($rows as $row) {
                        // Inward Product save
                        InwardProductRepository::storeByInwardProductRequest($row, $inwardData->id);

                        // Design Master update (only for normal inward entries, skip for Kachi entries)
                        if (!$isKachi) {
                            DesignMasterRepository::designMasterByInwardProductupdate($row, $row['designid']);
                        }
                    }
                });

                $msg = $isKachi ? __('Kachi Entry created successfully!') : __('Inward Product created successfully!');

                return response()->json([
                    'status' => true,
                    'message' => $msg,
                ]);
            } catch (\Exception $e) {
//dd($e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong! ',
                ]);
            }
        }else{
            return response()->json([
                'status' => true,
                'message' => __('No inward products provided to create. Please add at least one row.'),
            ]);
        }


    }

    public function inwardProduct(Request $request)
    {
        $search = $request->search;
        $isKachi = $request->is_kachi;

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $inwardLists = InwardInvoice::select('id','shop_id','counter_master_id','inward_voucher_no','inward_date','inward_challan_no','inward_challan_date','inward_party_code','inward_acc_gst_amount','inward_acc_net_amount','inward_acc_amt_with_gst','is_kachi')
            ->with(['counter:id,code,counter_name','partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no','inwardProduct','inwardProduct.products:id,name','inwardProduct.designMaster:id,design_number','inwardProduct.vatTax:id,name,percentage','inwardProduct.hsnMaster:id,hsn_code'])
            ->when($search, function ($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('inward_voucher_no', 'like', "%$search%")
                        ->orWhere('inward_challan_no', 'like', "%$search%")
                        ->orWhereHas('partyCode', function($partyQuery) use ($search) {
                            $partyQuery->where('accountName', 'like', "%$search%");
                        });
                });
            })
            ->when($isKachi !== null && $isKachi !== '', function($query) use ($isKachi) {
                if ($isKachi == 1 || $isKachi === '1' || $isKachi === true || $isKachi === 'true') {
                    return $query->where('is_kachi', 1);
                } else {
                    return $query->where(function($q) {
                        $q->where('is_kachi', 0)->orWhereNull('is_kachi');
                    });
                }
            }, function($query) {
                // Default: show ONLY normal/final inward entries
                return $query->where(function($q) {
                    $q->where('is_kachi', 0)->orWhereNull('is_kachi');
                });
            })
            ->where('shop_id',$shop->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return view('shop.inward-product.partials.inward-list', compact('inwardLists'))->render();
        }

        return view('shop.inward-product.partials.inward-list', compact('inwardLists'));
    }

    public function edit($id)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $inwardData = InwardInvoice::with(['counter:id,code,counter_name','vattax:id,name,percentage','partyCode:id,accountName,accountshortcode,other_info_act_limit','inwardProduct','purchaser:id,name,last_name','season:id,name','agent:id,code,name','transport:id,code,name','deliveryBy:id,name','inwardProduct.products:id,name','inwardProduct.colors','inwardProduct.sizes','inwardProduct.designMaster:id,design_number','inwardProduct.hsnMaster:id,hsn_code','inwardProduct.vatTax:id,percentage'])->where('id',$id)->where('shop_id',$shop->id)->first();

//        $inwardData->load([
//            'products:id,name',
//            'accountMasters:id,accountshortcode,accountName'
//        ]);
//        dd($inwardData);
        return response()->json([
            'inwardData' => $inwardData,
        ]);
    }

    public function update(InwardProductRequest $request, InwardInvoice $inwardProduct)
    {
        $rows = $request->rows;
        $invoiceData = $request->invoiceData;
        if (!empty($rows) && !empty($invoiceData)){
            if ($inwardProduct->is_purchase){
                return response()->json([
                    'status' => false,
                    'message' => __('already purchased cannot edited'),
                ]);
            }
            try {
                DB::transaction(function () use ($rows, $invoiceData, $inwardProduct) {

                    /** -------------------------
                     * 1️⃣ UPDATE INVOICE HEADER
                     * ------------------------- */
                    InwardInvoiceRepository::updateByInwardInvoiceRequest($inwardProduct, $invoiceData);

                    $existingProductIds = [];

                    /** -------------------------
                     * 2️⃣ LOOP ROWS
                     * ------------------------- */
                    foreach ($rows as $row) {
                        // EDIT ROW

                        if (
                            empty($row['itemid']) &&
                            empty($row['designid']) &&
                            empty($row['qty'])
                        ) {
                            continue;
                        }

                        if (!empty($row['inwardProductId'])) {

                            $product = InwardProduct::find($row['inwardProductId']);

                            if ($product) {
                                InwardProductRepository::updateByInwardProductRequest($product, $row);
                                $existingProductIds[] = $product->id;
                            }

                        } else {
                            // NEW ROW
                            $product = InwardProductRepository::storeByInwardProductRequest(
                                $row,
                                $inwardProduct->id
                            );

                            $existingProductIds[] = $product->id;
                        }

                        // Design Master Update
                        DesignMasterRepository::designMasterByInwardProductupdate(
                            $row,
                            $row['designid']
                        );
                    }
                    //
                    //            /** -------------------------
                    //             * 3️⃣ DELETE REMOVED ROWS
                    //             * ------------------------- */
                    //            InwardProduct::where('inward_invoice_id', $inwardProduct->id)
                    //                ->whereNotIn('id', $existingProductIds)
                    //                ->delete();

                });
                return response()->json([
                    'status' => true,
                    'message' => __('Inward Product updated successfully!'),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong! ' . $e,
                ]);
            }
        }else{
            return response()->json([
                'status' => true,
                'message' => __('No inward products provided to create. Please add at least one row.'),
            ]);
        }


    }

    public function destroy($id)
    {
        $inwardProduct = InwardInvoice::find($id);

        if (!$inwardProduct) {
            return response()->json(['error' => 'Inward product not found'], 404);
        }

        $inwardProduct->delete();

        return response()->json(['success' => 'Inward product deleted successfully']);
    }

    public function destroyInwardProduct($id)
    {
        $inwardProduct = InwardProduct::find($id);

        if (!$inwardProduct) {
            return response()->json(['error' => 'Inward product not found'], 404);
        }

        $inwardProduct->delete();

        return response()->json(['success' => 'Inward product deleted successfully']);
    }

    public function inwardGetByProductBarcode($id)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        if (!empty($id) && $id !== 0){
//            $inwardBarcodeData = InwardInvoice::with(['vattax:id,name,percentage','inwardProduct','inwardProduct.products:id,name','inwardProduct.colors','inwardProduct.sizes','inwardProduct.designMaster:id,design_number','inwardProduct.hsnMaster:id,hsn_code','inwardProduct.vatTax:id,percentage'])->where('id',$id)->where('shop_id',$shop->id)->first();

            $inwardBarcodeData = InwardInvoice::with([
                'vattax:id,name,percentage',
                'inwardProduct' => function ($query) {
                    $query->withCount('barcodes')
                    ->with([
                        'products:id,name',
                        'colors',
                        'sizes',
                        'designMaster:id,design_number',
                        'hsnMaster:id,hsn_code',
                        'vatTax:id,percentage'
                    ]);
                }
            ])
                ->where('id', $id)
                ->where('shop_id', $shop->id)
                ->first();

            return response()->json([
                'inwardBarcodeData' => $inwardBarcodeData,
            ]);
        }

        return response()->json(['error' => 'Inward barcode not found'], 404);

    }

    public function productWiseBarcodeGenerate(Request $request, $inwardInvoiceId, $inwardProductId, $productId)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        if (!empty($inwardInvoiceId) && $inwardInvoiceId !== 0 && !empty($inwardProductId) && $inwardProductId !== 0 && !empty($productId) && $productId !== 0){

            $inwardBarcodeData = InwardInvoice::with([
                'inwardProduct' => function ($query) use ($inwardProductId, $productId) {
                    $query->where('id', $inwardProductId)
                        ->where('product_id', $productId);
                },
                'inwardProduct.products:id,name'
            ])
                ->where('id', $inwardInvoiceId)
                ->first();

            $totalQty = $inwardBarcodeData->inwardProduct[0]->quantity;

            if ($totalQty <= 0) {
                return response()->json(['error' => 'Invalid quantity'], 400);
            }

            // Find how many are already generated
            $generatedCount = ProductBarcode::where('inward_product_id', $inwardProductId)->count();
            $maxAllowed = $totalQty - $generatedCount;

            $generateQty = (int)$request->input('qty', $maxAllowed);

            if ($generateQty <= 0 || $generateQty > $maxAllowed) {
                return response()->json(['error' => 'Quantity exceeds limit or is invalid'], 400);
            }

            DB::beginTransaction();
            try {
                $lastBarcode = ProductBarcode::orderBy('id', 'desc')
                    ->value('barcode_number');

                $startNumber = $lastBarcode ? (int)$lastBarcode + 1 : 1;

                $insertData = [];
                $chunkSize = 1000;

                for ($i = 0; $i < $generateQty; $i++) {
                    $barcodeNumber = str_pad($startNumber + $i, 10, '0', STR_PAD_LEFT);

                    $insertData[] = [
                        'shop_id' => $shop->id,
                        'inward_invoice_id' => $inwardBarcodeData->id,
                        'product_id' => $inwardBarcodeData->inwardProduct[0]?->products?->id,
                        'inward_product_id' => $inwardBarcodeData->inwardProduct[0]?->id,
                        'barcode_number' => $barcodeNumber,
                        'mrp' => $inwardBarcodeData->inwardProduct[0]?->mrp,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($insertData) === $chunkSize) {
                        ProductBarcode::insert($insertData);
                        $insertData = [];
                    }
                }
                if (!empty($insertData)) {
                    ProductBarcode::insert($insertData);
                }

                // Increment Product quantity by generated amount so online shop stock increases!
                $productRecord = Product::find($productId);
                if ($productRecord) {
                    $productRecord->increment('quantity', $generateQty);
                }

                DB::commit();
                return response()->json(['success' => 'Barcode generate successfully'], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to generate barcode: ' . $e->getMessage()], 500);
            }
        }
        return response()->json(['error' => 'Barcode generate fail'], 404);
    }

    public function productByBarcodeGet($inwardInvoiceId, $inwardProductId, $productId)
    {
        if (!empty($inwardInvoiceId) && $inwardInvoiceId !== 0 && !empty($inwardProductId) && $inwardProductId !== 0 && !empty($productId) && $productId !== 0){

                    $barcodeDatas = ProductBarcode::with([
                        'inwardInvoice' => function ($query) {
                            $query->with('partyCode:id,accountName');
                        },
                        'inwardProduct' => function ($query) {
                            $query->with([
                                'products:id,name',
                                'colors',
                                'sizes',
                                'designMaster:id,design_number',
                            ]);
                        },
                        'orderProduct.order'
                    ])

                ->where('inward_invoice_id', $inwardInvoiceId)
                ->where('inward_product_id', $inwardProductId)
                ->where('product_id', $productId)
                ->paginate(20);

//dd($barcodeDatas);
            return view('shop.barcode.index',compact('barcodeDatas'));
        }
        return back()->withSuccess(__('Barcode load fail'));

    }

    public function productBarcodeGenerate($id)
    {
        if (!empty($id) && $id !== 0){

            $barcodeGenerate = ProductBarcode::with([
                'inwardInvoice' => function ($query) {
                    $query->with('partyCode:id,accountName');
                },
                'inwardProduct' => function ($query) {
                    $query->with([
                        'products:id,name',
                        'colors',
                        'sizes',
                        'designMaster:id,design_number',
                    ]);
                }
            ])->where('id',$id)->first();


            $buyPrice = $barcodeGenerate->inwardProduct->buy_price;
            $roundedPrice = round($buyPrice);
            $masterPrices = MasterPrice::pluck('alphabet','number')->toArray();

            $priceString = (string)$roundedPrice;
            $encodedPrice = '';
            foreach (str_split($priceString) as $digit) {
                if(isset($masterPrices[$digit])){
                    $encodedPrice .= $masterPrices[$digit];
                }
            }


            $barcodeGenerate->is_printed = 1;
            $barcodeGenerate->save();

            $barcode = new DNS1D();

            $barcodeImage = $barcode->getBarcodePNG(
                $barcodeGenerate->barcode_number,
                'C128',
            );

            return view('shop.barcode.generate',compact('barcodeGenerate','barcodeImage','encodedPrice'));
        }

        return back()->withSuccess(__('Barcode generate fail'));
    }

    public function productBarcodeGenerateMultiple(Request $request)
    {
//        $ids = $request->barcode_ids;
        $ids = array_filter($request->barcode_ids ?? []);

        if (count($ids) < 1) {
            return back()->withError(__('Please select at least one item'));
        }

        $barcode = new DNS1D();

        $masterPrices = MasterPrice::pluck('alphabet','number')->toArray();

        $barcodeGenerate = collect();

        ProductBarcode::with([
            'inwardInvoice.partyCode:id,accountName',
            'inwardProduct.products:id,name',
            'inwardProduct.colors',
            'inwardProduct.sizes',
            'inwardProduct.designMaster:id,design_number',
        ])
            ->whereIn('id', $ids)
            ->chunk(500, function ($items) use ($barcode, &$barcodeGenerate, $masterPrices) {

                foreach ($items as $item) {
                    $item->barcode_image = $barcode->getBarcodePNG(
                        $item->barcode_number,
                        'C128'
                    );

                    $buyPrice = $item->inwardProduct->buy_price ?? 0;

                    // ✅ Round karo (agar margin add karna ho to yaha karo)
                    $roundedPrice = round($buyPrice);
                    // ✅ Encode karo
                    $encodedPrice = '';
                    foreach (str_split((string)$roundedPrice) as $digit) {
                        if(isset($masterPrices[$digit])){
                            $encodedPrice .= $masterPrices[$digit];
                        }
                    }

                    $item->encoded_price = $encodedPrice;
                }

                $barcodeGenerate = $barcodeGenerate->merge($items);
            });

        ProductBarcode::whereIn('id', $ids)->update(['is_printed' => 1]);

        return view('shop.barcode.generate_multiple', compact('barcodeGenerate'));
    }

    public function getOldProductPrice($id)
    {
        $shop = generaleSetting('shop');

        if (!empty($id) && $id != 0 && $id != ''){
            $oldPrices = InwardProduct::where('design_master_id', $id)
                ->where('shop_id', $shop->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'created_at' => $item->created_at->format('d/m/Y'),
                        'buy_price' => $item->buy_price,
                        'mrp' => $item->mrp,
                    ];
                });

            return response()->json($oldPrices);

        }
    }


}
