<?php

namespace App\Http\Controllers\Shop;

use App\Events\AdminProductRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\InwardProductRequest;
use App\Models\Color;
use App\Models\CounterMaster;
use App\Models\DesignMaster;
use App\Models\HsnMaster;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use App\Models\MasterPrice;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Size;
use App\Models\VatTax;
use App\Models\AccountMaster;
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
        $shop?->load('state:id,name');
        $rootShop = generaleSetting('rootShop');

        $dayBooks = CounterMaster::all();
        $inwardTaxs = VatTax::all();

        return view('shop.inward-product.index',compact('dayBooks','inwardTaxs','shop'));
    }


    public function designDataGet(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $shopIds = array_unique(array_filter([1, 14, $shop?->id, $rootShop?->id]));

        $itemSearch = $request->itemSearch ?? ($request->search ?? '');
        $colorSearch = $request->colorSearch;
        $sizeSearch = $request->sizeSearch;
        $taxCodeSearch = $request->taxCodeSearch;
        $designNoSearch = $request->designNoSearch ?? '';

        if ($request->has('colorSearch')){
            $term = trim($request->colorSearch ?? '');
            $designWithColorData = Color::where('is_active', 1)
                ->when($term !== '', function($q) use ($term) {
                    $q->where(function($sq) use ($term) {
                        $sq->where('name', 'like', "%{$term}%")
                           ->orWhere('color_code', 'like', "%{$term}%");
                    });
                })
                ->orderByRaw("CASE WHEN name = ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END", [$term, "{$term}%"])
                ->orderBy('name', 'ASC')
                ->limit(150)
                ->get(['id', 'name', 'color_code']);

            return response()->json([
                'designWithColorData' => $designWithColorData,
                'status' => true
            ]);
        }

        if ($request->has('sizeSearch')){
            $term = trim($request->sizeSearch ?? '');
            $designWithSizeData = Size::where('is_active', true)
                ->when($term !== '', function($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%");
                })
                ->orderByRaw("CASE WHEN name = ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END", [$term, "{$term}%"])
                ->orderByRaw("CAST(name AS UNSIGNED) ASC, name ASC")
                ->limit(200)
                ->get(['id', 'name']);

            return response()->json([
                'designWithSizeData' => $designWithSizeData,
                'status' => true
            ]);
        }

        if ($request->has('taxCodeSearch')){
            $term = trim($request->taxCodeSearch ?? '');
            $designWithtaxCodeData = HsnMaster::where('is_active', true)
                ->with([
                    'vattax:id,percentage',
                    'subHsn.vattax:id,name,percentage'
                ])
                ->when($term !== '', function($q) use ($term) {
                    $q->where('hsn_code', 'like', "%{$term}%");
                })
                ->orderByRaw("CASE WHEN hsn_code = ? THEN 0 WHEN hsn_code LIKE ? THEN 1 ELSE 2 END", [$term, "{$term}%"])
                ->orderBy('hsn_code', 'ASC')
                ->limit(100)
                ->get(['id', 'hsn_code', 'vat_tax_id']);

            return response()->json([
                'designWithtaxCodeData' => $designWithtaxCodeData,
                'status' => true
            ]);
        }

        if ($request->has('itemSearch') || $request->has('designNoSearch') || $request->has('search') || !empty($itemSearch) || !empty($designNoSearch)){
            $searchTerm = !empty($itemSearch) ? $itemSearch : $designNoSearch;

            $designWithItemData = $shop?->designMasters()->active()
                ->when($searchTerm, function ($query) use ($searchTerm) {
                    $query->where(function($q) use ($searchTerm) {
                        $q->whereHas('products', function($pq) use ($searchTerm) {
                            $pq->where('name', 'like', "%$searchTerm%");
                        })
                        ->orWhere('design_number', 'like', "%$searchTerm%");
                    });
                })
                ->with([
                    'products:id,name,hsn_master_id,vat_tax_id',
                    'products.hsnMaster:id,hsn_code,vat_tax_id',
                    'products.hsnMaster.subHsn.vattax:id,name,percentage',
                    'products.vatTax:id,percentage'
                ])
                ->latest()
                ->limit(40)
                ->get() ?? collect();

            $suggestions = collect();
            $seenProductIds = [];

            // Add existing designs to suggestions
            foreach ($designWithItemData as $design) {
                if ($design->products) {
                    $seenProductIds[] = $design->products->id;
                }
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
            $matchingProducts = Product::whereIn('shop_id', $shopIds)
                ->when($searchTerm, function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%$searchTerm%");
                })
                ->whereNotIn('id', $seenProductIds)
                ->with([
                    'hsnMaster:id,hsn_code,vat_tax_id',
                    'hsnMaster.subHsn.vattax:id,name,percentage',
                    'vatTax:id,percentage'
                ])
                ->latest()
                ->limit(20)
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

            return response()->json([
                'designWithItemData' => $suggestions,
                'status' => true
            ]);
        }

        return response()->json([
            'message' => 'No data found',
            'status' => false
        ]);
    }

    public function store(InwardProductRequest $request)
    {
        $shop = generaleSetting('shop');


        if (!empty($request->rows)) {
            // Inward Invoice Save
            $inwardData = InwardInvoiceRepository::storeByInwardInvoiceRequest($request->invoiceData);

            $rows = $request->rows;
            $partyId = $inwardData->inward_party_code ?? ($request->invoiceData['inward_party_code'] ?? null);
            $partyName = null;
            if (!empty($partyId)) {
                $party = AccountMaster::find($partyId);
                $partyName = $party?->accountName;
            }

            try {
                DB::transaction(function() use ($rows, $inwardData, $partyId, $partyName) {
                    foreach ($rows as $row) {
                        // Inward Product save
                        InwardProductRepository::storeByInwardProductRequest($row, $inwardData->id);

                        // Design Master update
                        DesignMasterRepository::designMasterByInwardProductupdate(
                            $row,
                            $row['designid'] ?? null,
                            $partyId,
                            $partyName
                        );
                    }
                });

                return response()->json([
                    'status' => true,
                    'message' => __('Inward Product created successfully!'),
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

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');


//        $inwardLists = InwardProduct::with(['products:id,name','designMaster:id,design_number'])->when($search, function ($query) use ($search) {
//            return $query->where('name', 'like', "%$search%");
//        })->where('shop_id',$shop->id)->orderByDesc('id')->paginate(20)->withQueryString();

        $inwardLists = InwardInvoice::select('id','shop_id','counter_master_id','inward_voucher_no','inward_date','inward_challan_no','inward_challan_date','inward_party_code','inward_acc_gst_amount','inward_acc_net_amount','inward_acc_amt_with_gst')->with(['counter:id,code,counter_name','partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no','inwardProduct','inwardProduct.products:id,name','inwardProduct.designMaster:id,design_number','inwardProduct.vatTax:id,name,percentage','inwardProduct.hsnMaster:id,hsn_code'])->when($search, function ($query) use ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('inward_voucher_no', 'like', "%$search%")
                    ->orWhere('inward_challan_no', 'like', "%$search%")
                    ->orWhereHas('partyCode', function($partyQuery) use ($search) {
                        $partyQuery->where('accountName', 'like', "%$search%");
                    });
            });
        })->where('shop_id',$shop->id)->orderByDesc('id')->paginate(20)->withQueryString();
//dd($inwardLists);
        if ($request->ajax()) {
            return view('shop.inward-product.partials.inward-list', compact('inwardLists'))->render();
        }
    }

    public function edit($id)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $inwardData = InwardInvoice::with([
            'counter:id,code,counter_name',
            'vattax:id,name,percentage',
            'partyCode:id,accountName,accountshortcode,other_info_act_limit,state_id',
            'inwardProduct' => function ($q) {
                $q->where('is_purchase_only', false);
            },
            'purchaser:id,name,last_name',
            'season:id,name',
            'agent:id,code,name',
            'transport:id,code,name',
            'deliveryBy:id,name',
            'inwardProduct.products:id,name',
            'inwardProduct.colors',
            'inwardProduct.sizes',
            'inwardProduct.designMaster:id,design_number',
            'inwardProduct.hsnMaster:id,hsn_code',
            'inwardProduct.vatTax:id,percentage'
        ])->where('id',$id)->where('shop_id',$shop->id)->first();

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
            $partyId = $invoiceData['inward_party_code'] ?? $inwardProduct->inward_party_code;
            $partyName = null;
            if (!empty($partyId)) {
                $party = AccountMaster::find($partyId);
                $partyName = $party?->accountName;
            }

            try {
                DB::transaction(function () use ($rows, $invoiceData, $inwardProduct, $partyId, $partyName) {

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
                            $row['designid'] ?? null,
                            $partyId,
                            $partyName
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
                    $query->where('is_purchase_only', false)
                    ->withCount('barcodes')
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
                $prefix = date('y') . date('m');

                $lastBarcode = ProductBarcode::where('barcode_number', 'like', $prefix . '%')
                    ->orderBy('barcode_number', 'desc')
                    ->value('barcode_number');

                $currentSeq = 1;
                if ($lastBarcode && strlen($lastBarcode) >= 11) {
                    $seq = (int)substr($lastBarcode, 4);
                    if ($seq > 0) {
                        $currentSeq = $seq + 1;
                    }
                }

                $insertData = [];
                $chunkSize = 1000;

                for ($i = 0; $i < $generateQty; $i++) {
                    $barcodeNumber = $prefix . str_pad($currentSeq + $i, 7, '0', STR_PAD_LEFT);

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

    /**
     * Generate barcodes for all items in an inward invoice in a single click
     */
    public function generateAllBarcodes(Request $request, $inwardInvoiceId)
    {
        $shop = generaleSetting('shop');
        if (empty($inwardInvoiceId) || $inwardInvoiceId == 0) {
            return response()->json(['error' => 'Invalid invoice ID'], 400);
        }

        $inwardInvoice = InwardInvoice::with([
            'inwardProduct' => function ($query) {
                $query->withCount('barcodes')->with('products:id,name');
            }
        ])
            ->where('id', $inwardInvoiceId)
            ->where('shop_id', $shop->id)
            ->first();

        if (!$inwardInvoice) {
            return response()->json(['error' => 'Inward invoice not found'], 404);
        }

        $selectedInwardProductIds = $request->input('inward_product_ids');
        if (!empty($selectedInwardProductIds)) {
            if (is_string($selectedInwardProductIds)) {
                $selectedInwardProductIds = explode(',', $selectedInwardProductIds);
            }
            $selectedInwardProductIds = array_filter(array_map('trim', (array)$selectedInwardProductIds));
        }

        DB::beginTransaction();
        try {
            $prefix = date('y') . date('m');

            $lastBarcode = ProductBarcode::where('barcode_number', 'like', $prefix . '%')
                ->orderBy('barcode_number', 'desc')
                ->value('barcode_number');

            $currentSeq = 1;
            if ($lastBarcode && strlen($lastBarcode) >= 11) {
                $seq = (int)substr($lastBarcode, 4);
                if ($seq > 0) {
                    $currentSeq = $seq + 1;
                }
            }

            $totalGenerated = 0;
            $insertData = [];
            $chunkSize = 500;

            foreach ($inwardInvoice->inwardProduct()->where('is_purchase_only', false)->get() as $item) {
                if (!empty($selectedInwardProductIds) && !in_array($item->id, $selectedInwardProductIds)) {
                    continue;
                }

                $existingCount = $item->barcodes_count ?? ProductBarcode::where('inward_product_id', $item->id)->count();
                $needed = $item->quantity - $existingCount;

                if ($needed <= 0) {
                    continue;
                }

                for ($i = 0; $i < $needed; $i++) {
                    $barcodeNumber = $prefix . str_pad($currentSeq, 7, '0', STR_PAD_LEFT);
                    $currentSeq++;

                    $insertData[] = [
                        'shop_id' => $shop->id,
                        'inward_invoice_id' => $inwardInvoice->id,
                        'product_id' => $item->product_id,
                        'inward_product_id' => $item->id,
                        'barcode_number' => $barcodeNumber,
                        'mrp' => $item->mrp,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $totalGenerated++;

                    if (count($insertData) >= $chunkSize) {
                        ProductBarcode::insert($insertData);
                        $insertData = [];
                    }
                }

                // Increment product quantity for online shop stock
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('quantity', $needed);
                }
            }

            if (!empty($insertData)) {
                ProductBarcode::insert($insertData);
            }

            DB::commit();

            return response()->json([
                'success' => $totalGenerated > 0 
                    ? "Successfully generated {$totalGenerated} barcodes in Bill #{$inwardInvoice->inward_voucher_no}!"
                    : "All items in this bill already have barcodes generated.",
                'total_generated' => $totalGenerated,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to generate all barcodes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print all or selected barcodes for an inward invoice in a single sheet (2-up side-by-side)
     */
    public function printMultipleInwardBarcodes(Request $request, $inwardInvoiceId)
    {
        $shop = generaleSetting('shop');
        if (empty($inwardInvoiceId) || $inwardInvoiceId == 0) {
            return back()->withError(__('Invalid invoice ID'));
        }

        $inwardInvoice = InwardInvoice::where('id', $inwardInvoiceId)
            ->where('shop_id', $shop->id)
            ->first();

        if (!$inwardInvoice) {
            return back()->withError(__('Inward invoice not found'));
        }

        $selectedInwardProductIds = $request->input('inward_product_ids');
        if (!empty($selectedInwardProductIds)) {
            if (is_string($selectedInwardProductIds)) {
                $selectedInwardProductIds = explode(',', $selectedInwardProductIds);
            }
            $selectedInwardProductIds = array_filter(array_map('trim', (array)$selectedInwardProductIds));
        }

        $query = ProductBarcode::with([
            'inwardInvoice.partyCode:id,accountName',
            'inwardProduct.products:id,name',
            'inwardProduct.colors',
            'inwardProduct.sizes',
            'inwardProduct.designMaster:id,design_number',
        ])
            ->where('inward_invoice_id', $inwardInvoiceId)
            ->where('is_sold', 0);

        if (!empty($selectedInwardProductIds)) {
            $query->whereIn('inward_product_id', $selectedInwardProductIds);
        }

        $query->orderBy('inward_product_id', 'asc')->orderBy('id', 'asc');

        $barcode = new DNS1D();
        $masterPrices = MasterPrice::pluck('alphabet', 'number')->toArray();
        $barcodeGenerate = collect();

        $query->chunk(500, function ($items) use ($barcode, &$barcodeGenerate, $masterPrices) {
            foreach ($items as $item) {
                $item->barcode_image = $barcode->getBarcodePNG(
                    $item->barcode_number,
                    'C128'
                );

                $buyPrice = $item->inwardProduct->buy_price ?? 0;
                $roundedPrice = round($buyPrice);
                $encodedPrice = '';
                foreach (str_split((string)$roundedPrice) as $digit) {
                    if (isset($masterPrices[$digit])) {
                        $encodedPrice .= $masterPrices[$digit];
                    }
                }

                $item->encoded_price = $encodedPrice;
            }

            $barcodeGenerate = $barcodeGenerate->merge($items);
        });

        if ($barcodeGenerate->isEmpty()) {
            return back()->withError(__('No barcodes found to print. Please click "Generate All Barcodes" first.'));
        }

        // Mark as printed
        ProductBarcode::whereIn('id', $barcodeGenerate->pluck('id'))->update(['is_printed' => 1]);

        return view('shop.barcode.generate_multiple', compact('barcodeGenerate'));
    }

}
