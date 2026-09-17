<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\VatTax;
use App\Models\AccountMaster;
use App\Repositories\AccountMasterRepository;
use App\Http\Requests\AccountMasterRequest;
use Illuminate\Http\Request;
use App\Services\GstVerificationService;

class AccountMasterController extends Controller
{
    protected $gstService;

    public function __construct(GstVerificationService $gstService)
    {
        $this->gstService = $gstService;
    }

    public function index(Request $request)
    {
        $rootShop = generaleSetting('rootShop');
        $currentShop = generaleSetting('shop');
        $shopIds = array_filter(array_unique([$rootShop?->id, $currentShop?->id, 1, 14]));
        $search = $request->search ?? null;

        $accountMasters = AccountMaster::basicFields()
            ->with(['account:id,name,code', 'city:id,name'])
            ->whereIn('shop_id', $shopIds)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('accountName', 'like', '%' . $search . '%')
                      ->orWhere('tax_info_gst_no', 'like', '%' . $search . '%')
                      ->orWhere('cont_info_mobile1', 'like', '%' . $search . '%')
                      ->orWhere('contperson', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('shop.account-master.index', compact('accountMasters', 'search'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
        $accounts = Account::active()->get();


        $citys = City::whereHas('state', function ($query) {
            $query->where('country_id', 101);
        })->orderBy('name')->whereIn('name', ['Ahmedabad','Mumbai', 'Delhi', 'Gandhinagar'])->get(['id', 'name'])->unique('name')->values();

        $countrys = Country::orderBy('name')->whereIn('name', ['India'])->get(['id', 'name'])->unique('name')->values();

        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);

        return view('shop.account-master.create',compact('accounts','citys','countrys','taxs'));
    }

    public function store(AccountMasterRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);
        $request->merge([
            'cont_info_send_sms' => $request->has('cont_info_send_sms') ? 1 : 0,
            'cont_info_dndactivate' => $request->has('cont_info_dndactivate') ? 1 : 0,
            'is_party_code' => $request->has('is_party_code') ? 1 : 0,

            'tax_info_tds_deduct' => $request->has('tax_info_tds_deduct') ? 1 : 0,
            'tax_info_tcs_deduct' => $request->has('tax_info_tcs_deduct') ? 1 : 0,
        ]);
        AccountMasterRepository::accountMasterCreate($request);
        return to_route('shop.accountMaster.index')->withSuccess(__('Account master created successfully'));
    }

    public function edit(AccountMaster $accountMaster)
    {
        $shop = generaleSetting('shop');
        $accounts = Account::active()->get();


        $citys = City::whereHas('state', function ($query) {
            $query->where('country_id', 101);
        })->orderBy('name')->whereIn('name', ['Ahmedabad','Mumbai', 'Delhi', 'Gandhinagar'])->get(['id', 'name'])->unique('name')->values();

        $countrys = Country::orderBy('name')->whereIn('name', ['India'])->get(['id', 'name'])->unique('name')->values();

        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);

        return view('shop.account-master.edit',compact('accountMaster','accounts','citys','countrys','taxs'));
    }

    public function update(AccountMasterRequest $request, AccountMaster $accountMaster)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);
        $request->merge([
            'cont_info_send_sms' => $request->has('cont_info_send_sms') ? 1 : 0,
            'cont_info_dndactivate' => $request->has('cont_info_dndactivate') ? 1 : 0,
            'is_party_code' => $request->has('is_party_code') ? 1 : 0,

            'tax_info_tds_deduct' => $request->has('tax_info_tds_deduct') ? 1 : 0,
            'tax_info_tcs_deduct' => $request->has('tax_info_tcs_deduct') ? 1 : 0,
        ]);

        AccountMasterRepository::accountMasterUpdate($request, $accountMaster);

        return to_route('shop.accountMaster.index')->withSuccess(__('Account master updated successfully'));
    }

    public function statusToggle(AccountMaster $accountMaster)
    {
        $accountMaster->update([
            'is_active' => ! $accountMaster->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    public function partyCodeToggle(AccountMaster $accountMaster)
    {
        $accountMaster->update([
            'is_party_code' => ! $accountMaster->is_party_code,
        ]);

        return back()->withSuccess(__('Party Code status updated successfully'));
    }

    public function citiesSearch(Request $request)
    {
        $search = $request->get('q');

        $cities = City::whereHas('state', fn($q) => $q->where('country_id', 101))
            ->when($search,
                fn($q) => $q->where('name', 'like', "%$search%"),
                fn($q) => $q->whereIn('name', ['Ahmedabad','Mumbai', 'Delhi', 'Gandhinagar'])
            )
            ->orderBy('name')
            ->get(['id', 'name'])
            ->unique('name')
            ->values();

        return response()->json($cities);
    }

    public function countrySearch(Request $request)
    {
        $search = $request->get('q');

        $countrys = Country::when($search,
            fn($q) => $q->where('name', 'like', "%$search%"),
            fn($q) => $q->whereIn('name', ['India'])
        )
            ->orderBy('name')
            ->get(['id', 'name'])
            ->unique('name')
            ->values();

        return response()->json($countrys);
    }

    public function getStates(Request $request)
    {
        $states = State::where('country_id', $request->country_id)
            ->orderBy('name')
            ->get(['id','name'])
            ->unique('name')
            ->values();

        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)
            ->orderBy('name')
            ->get(['id','name'])
            ->unique('name')
            ->values();

        return response()->json($cities);
    }

    public function check(Request $request)
    {
        $gstin = $request->gstin;

        $result = $this->gstService->verify($gstin);

        return response()->json($result);
    }

}
