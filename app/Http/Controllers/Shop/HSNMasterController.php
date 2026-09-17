<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\HSNMasterRequest;
use App\Models\HsnSubMaster;
use App\Repositories\HsnMasterRepository;
use App\Models\HsnMaster;
use App\Models\VatTax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HSNMasterController extends Controller
{
    public function index(Request $request)
    {
        $rootShop = generaleSetting('rootShop');
        $currentShop = generaleSetting('shop');
        $shopIds = array_filter(array_unique([$rootShop?->id, $currentShop?->id, 1, 14]));
        $search = $request->search ?? null;

        $hsnMasters = HsnMaster::basicFields()
            ->with('vattax:id,name,percentage')
            ->whereIn('shop_id', $shopIds)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('hsn_code', 'like', '%' . $search . '%')
                      ->orWhere('hsn_description', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.hsn-master.index', compact('hsnMasters', 'search'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);
        return view('shop.hsn-master.create',compact('taxs'));
    }

//    public function store(HSNMasterRequest $request)
//    {
//        $shop = generaleSetting('shop');
//        $request->merge(['shop_id' => $shop->id]);
//
//        HsnMasterRepository::hsnMasterCreate($request);
//        return to_route('shop.hsnMaster.index')->withSuccess(__('HSN master created successfully'));
//    }

    public function store(HSNMasterRequest $request)
    {
        $shop = generaleSetting('shop');


        DB::transaction(function () use ($request, $shop) {

            $firstIndex = 0;

            $hsn = HsnMaster::create([
                'shop_id' => $shop->id,
                'hsn_code' => $request->hsn_code,
                'hsn_description' => $request->hsn_description,

                'vat_tax_id' => $request->vat_tax_id[$firstIndex],
                'from_sales_rate' => $request->from_sales_rate[$firstIndex],
                'to_sales_rate' => $request->to_sales_rate[$firstIndex],
                'from_purchase_rate' => $request->from_purchase_rate[$firstIndex],
                'to_purchase_rate' => $request->to_purchase_rate[$firstIndex],
                'from_date' => $request->from_date[$firstIndex] ?? null,
                'to_date' => $request->to_date[$firstIndex] ?? null,
            ]);

            foreach ($request->vat_tax_id as $key => $taxId) {
//                if ($key == 0) continue;

                HsnSubMaster::create([
                    'hsn_master_id' => $hsn->id,
                    'vat_tax_id' => $taxId,
                    'from_sales_rate' => $request->from_sales_rate[$key],
                    'to_sales_rate' => $request->to_sales_rate[$key],
                    'from_purchase_rate' => $request->from_purchase_rate[$key],
                    'to_purchase_rate' => $request->to_purchase_rate[$key],
                    'from_date' => $request->from_date[$key] ?? null,
                    'to_date' => $request->to_date[$key] ?? null,
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'HSN master created successfully'
        ]);
    }

    public function edit(HsnMaster $hsnMaster)
    {
//        $shop = generaleSetting('shop');
//        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);

        $shop = generaleSetting('shop');
        $hsnMaster->load('subHsn');
        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);

        return view('shop.hsn-master.edit',compact('taxs','hsnMaster'));
    }

//    public function update(HSNMasterRequest $request, HsnMaster $hsnMaster)
//    {
//        $shop = generaleSetting('shop');
//        $request->merge(['shop_id' => $shop->id]);
//
//        HsnMasterRepository::hsnMasterUpdate($request, $hsnMaster);
//
//        return to_route('shop.hsnMaster.index')->withSuccess(__('HSN master updated successfully'));
//    }

    public function update(HSNMasterRequest $request, HsnMaster $hsnMaster)
    {
        DB::transaction(function () use ($request, $hsnMaster) {

            // 🔥 Update Parent
            $hsnMaster->update([
                'hsn_code' => $request->hsn_code,
                'hsn_description' => $request->hsn_description,
                'vat_tax_id' => $request->vat_tax_id[0],
                'from_sales_rate' => $request->from_sales_rate[0],
                'to_sales_rate' => $request->to_sales_rate[0],
                'from_purchase_rate' => $request->from_purchase_rate[0],
                'to_purchase_rate' => $request->to_purchase_rate[0],
                'from_date' => $request->from_date[0] ?? null,
                'to_date' => $request->to_date[0] ?? null,
            ]);

            // 🔥 Existing sub ids
            $existingIds = $hsnMaster->subHsn()->pluck('id')->toArray();

            $keepIds = [];

            foreach ($request->row_id as $key => $rowId) {

//                if ($key == 0) continue;

                if ($rowId == 'new') {

                    // create new
                    HsnSubMaster::create([
                        'hsn_master_id' => $hsnMaster->id,
                        'vat_tax_id' => $request->vat_tax_id[$key],
                        'from_sales_rate' => $request->from_sales_rate[$key],
                        'to_sales_rate' => $request->to_sales_rate[$key],
                        'from_purchase_rate' => $request->from_purchase_rate[$key],
                        'to_purchase_rate' => $request->to_purchase_rate[$key],
                        'from_date' => $request->from_date[$key] ?? null,
                        'to_date' => $request->to_date[$key] ?? null,
                    ]);

                } else {

                    // update
                    $sub = HsnSubMaster::find($rowId);

                    if ($sub) {
                        $sub->update([
                            'vat_tax_id' => $request->vat_tax_id[$key],
                            'from_sales_rate' => $request->from_sales_rate[$key],
                            'to_sales_rate' => $request->to_sales_rate[$key],
                            'from_purchase_rate' => $request->from_purchase_rate[$key],
                            'to_purchase_rate' => $request->to_purchase_rate[$key],
                            'from_date' => $request->from_date[$key] ?? null,
                            'to_date' => $request->to_date[$key] ?? null,
                        ]);

                        $keepIds[] = $sub->id;
                    }
                }
            }

            // 🔥 delete removed rows
            $deleteIds = array_diff($existingIds, $keepIds);
            HsnSubMaster::whereIn('id', $deleteIds)->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'HSN updated successfully'
        ]);
    }

    public function statusToggle(HsnMaster $hsnMaster)
    {
        $hsnMaster->update([
            'is_active' => !$hsnMaster->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
