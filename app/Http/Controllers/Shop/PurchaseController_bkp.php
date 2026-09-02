<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPurchaseRequest;
use App\Models\InwardInvoice;
use App\Models\ProductPurchase;
use App\Models\VatTax;
use App\Repositories\InwardInvoiceRepository;
use App\Repositories\ProductPurchaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $dayBooks = $shop?->counter()->get();
//        dd($dayBooks,$rootShop,$shop);
        $inwardTaxs = VatTax::active()->get(['id', 'name', 'percentage']);
        return view('shop.purchase-product.index',compact('dayBooks','inwardTaxs'));
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

    public function store(ProductPurchaseRequest $request)
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

            DB::transaction(function () use ($purchaseData, $invoiceInward) {

                ProductPurchaseRepository::storeByProductPurchaseRequest(
                    $purchaseData,
                    $invoiceInward
                );

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

             dd($e->getMessage());

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

        $inwardLists = ProductPurchase::with([
            'inwardInvoice:id,inward_voucher_no,inward_date,inward_challan_no,inward_challan_date,inward_party_code,inward_acc_gst_amount,inward_acc_net_amount,inward_acc_amt_with_gst',
            'inwardInvoice.partyCode:id,accountName,accountshortcode,cont_info_mobile1,tax_info_gst_no'
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
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return view(
                'shop.purchase-product.partials.purchase-list',
                compact('inwardLists')
            )->render();
        }
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
}
