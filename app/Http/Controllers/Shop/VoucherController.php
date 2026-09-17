<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use Illuminate\Http\Request;
use App\Services\Accounting\VoucherService;
use App\Models\Voucher;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');

        // Always scope to the signed-in shop. shop_id used to be taken from the
        // request, which would have let one shop read another's ledger.
        $q = Voucher::with('entries')
            ->where('shop_id', $shop->id)
            ->when($request->voucher_type, fn($qq) => $qq->where('voucher_type', $request->voucher_type))
            ->when($request->financial_year_id, fn($qq) => $qq->where('financial_year_id', $request->financial_year_id))
            ->orderByDesc('date');

        return $q->paginate(20);
    }

    public function previewNumber(VoucherRequest $request, VoucherService $svc)
    {
//        $data = $request->validate([
//            'voucher_type' => 'required|string',
//            'shop_id' => 'required|exists:shops,id',
//            'financial_year_id' => 'required|exists:financial_years,id',
//            'seq_prefix' => 'nullable|string',
//            'seq_padding' => 'nullable|integer|min:3|max:12',
//        ]);
        $data = $request->validated();
        return $svc->previewNumber($data);
    }

    public function store(Request $request, VoucherService $svc)
    {
        $data = $request->validate([
            'voucher_type' => 'required|string',
            'date' => 'required|date',
            'narration' => 'nullable|string',
            'financial_year_id' => 'required|exists:financial_years,id',
            'seq_prefix' => 'nullable|string',
            'seq_padding' => 'nullable|integer|min:3|max:12',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.type' => 'required|in:Dr,Cr',
            'entries.*.amount' => 'required|numeric|min:0.01',
            'entries.*.description' => 'nullable|string',
        ]);
        $data['shop_id'] = generaleSetting('shop')->id;

        $voucher = $svc->create($data);

        return response()->json(['success'=>true,'voucher'=>$voucher], 201);
    }

    public function show($id)
    {
        return $this->ownedVoucher((int)$id)->load('entries');
    }

    public function update($id, Request $request, VoucherService $svc)
    {
        $data = $request->validate([
            'voucher_type' => 'sometimes|string',
            'date' => 'sometimes|date',
            'narration' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.type' => 'required|in:Dr,Cr',
            'entries.*.amount' => 'required|numeric|min:0.01',
            'entries.*.description' => 'nullable|string',
        ]);
        $this->ownedVoucher((int)$id);

        $voucher = $svc->update((int)$id, $data);

        return ['success'=>true,'voucher'=>$voucher];
    }

    public function reverse($id, VoucherService $svc)
    {
        $this->ownedVoucher((int)$id);

        // Returns the contra voucher, not the original.
        $contra = $svc->reverse((int)$id);

        return ['success'=>true,'voucher'=>$contra];
    }

    public function destroy($id, VoucherService $svc)
    {
        $this->ownedVoucher((int)$id);
        $svc->destroy((int)$id);

        return response()->json(['success'=>true]);
    }


    /** Fetch a voucher, refusing anything outside the signed-in shop. */
    protected function ownedVoucher(int $id): Voucher
    {
        return Voucher::where('id', $id)
            ->where('shop_id', generaleSetting('shop')->id)
            ->firstOrFail();
    }
}
