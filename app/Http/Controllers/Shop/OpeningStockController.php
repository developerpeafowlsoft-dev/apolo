<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\InwardProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Opening Stock register.
 *
 * Legacy stock imported from the desktop ERP keeps its original printed barcodes
 * and never passes through the Purchase stage, so it does not appear in the
 * Purchase Product List where the normal "Online Sale" toggle lives. This screen
 * is that toggle's counterpart for opening stock.
 */
class OpeningStockController extends Controller
{
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $search = trim((string)$request->input('search'));
        $online = $request->input('online');   // '1' | '0' | null
        $stock  = $request->input('stock');    // 'available' | 'sold' | null
        $linked = $request->input('linked');   // '1' resolved | '0' snapshot only | null
        $fy     = trim((string)$request->input('fy'));

        $base = InwardProduct::query()
            ->join('inward_invoices as i', 'inward_products.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shop->id)
            ->where('i.is_opening_stock', 1);

        // --- headline figures, always across the whole register ---------------
        $totals = (clone $base)
            ->selectRaw('COUNT(*) as line_count')
            ->selectRaw('COALESCE(SUM(inward_products.quantity),0) as units')
            ->selectRaw('COALESCE(SUM(inward_products.net_purc_price),0) as value_cost')
            ->selectRaw('COALESCE(SUM(inward_products.quantity * inward_products.mrp),0) as value_mrp')
            ->selectRaw('COALESCE(SUM(inward_products.is_online_product),0) as online_lines')
            ->first();

        $barcodeStats = DB::table('product_barcodes')
            ->where('shop_id', $shop->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(is_sold = 1) as sold')
            ->selectRaw('SUM(is_sold = 0) as available')
            ->first();

        // How much of the register is backed by a real inward challan. A line with
        // no challan still has its snapshot attributes; what it lacks is the GST
        // split and sales rate, so the split is worth showing rather than burying.
        $linkage = (clone $base)
            ->selectRaw('COALESCE(SUM(CASE WHEN i.legacy_voucher_resolved = 1 THEN inward_products.quantity ELSE 0 END),0) as linked_units')
            ->selectRaw('COALESCE(SUM(CASE WHEN i.legacy_voucher_resolved = 0 THEN inward_products.quantity ELSE 0 END),0) as unlinked_units')
            ->selectRaw('COALESCE(SUM(inward_products.sgst_amount + inward_products.cgst_amount + inward_products.igst_amount),0) as gst_total')
            ->first();

        // Financial years present, for the filter.
        $financialYears = (clone $base)
            ->whereNotNull('i.legacy_financial_year')
            ->distinct()
            ->orderBy('i.legacy_financial_year')
            ->pluck('i.legacy_financial_year')
            ->all();

        // --- the listing -------------------------------------------------------
        $rows = (clone $base)
            ->leftJoin('products as p', 'inward_products.product_id', '=', 'p.id')
            ->leftJoin('account_masters as am', 'i.inward_party_code', '=', 'am.id')
            ->leftJoin('hsn_masters as hm', 'inward_products.hsn_master_id', '=', 'hm.id')
            ->select([
                'inward_products.id',
                'inward_products.quantity',
                'inward_products.buy_price',
                'inward_products.mrp',
                'inward_products.net_purc_price',
                'inward_products.is_online_product',
                'inward_products.product_id',
                'p.name as product_name',
                'p.code as product_code',
                'am.accountName as party_name',
                'hm.hsn_code as hsn_code',
                'i.inward_voucher_no',
                'i.inward_challan_no',
                'i.inward_date',
                'i.legacy_financial_year',
                'i.legacy_party_gstin',
                'i.legacy_source',
                'i.legacy_voucher_resolved',
                'inward_products.net_purc_rate',
                'inward_products.purcost_rate',
                'inward_products.pur_exp_rate',
                'inward_products.mark_up',
                'inward_products.mark_down',
                'inward_products.tax_name',
                'inward_products.sgst_amount',
                'inward_products.cgst_amount',
                'inward_products.igst_amount',
                'inward_products.inward_sales_rate',
                'inward_products.inward_net_amount',
                'inward_products.legacy_line_match',
            ])
            ->selectRaw('(SELECT COUNT(*) FROM product_barcodes pb WHERE pb.inward_product_id = inward_products.id) as barcode_count')
            ->selectRaw('(SELECT COUNT(*) FROM product_barcodes pb WHERE pb.inward_product_id = inward_products.id AND pb.is_sold = 1) as sold_count')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('p.name', 'like', "%{$search}%")
                        ->orWhere('p.code', 'like', "%{$search}%")
                        ->orWhere('am.accountName', 'like', "%{$search}%")
                        ->orWhere('i.inward_voucher_no', 'like', "%{$search}%")
                        ->orWhere('i.inward_challan_no', 'like', "%{$search}%")
                        ->orWhere('i.legacy_party_gstin', 'like', "%{$search}%")
                        ->orWhereExists(function ($e) use ($search) {
                            $e->select(DB::raw(1))
                                ->from('product_barcodes as pb')
                                ->whereColumn('pb.inward_product_id', 'inward_products.id')
                                ->where('pb.barcode_number', $search);
                        });
                });
            })
            ->when($online !== null && $online !== '', fn ($q) => $q->where('inward_products.is_online_product', (int)$online))
            ->when($stock === 'available', fn ($q) => $q->whereRaw('(SELECT COUNT(*) FROM product_barcodes pb WHERE pb.inward_product_id = inward_products.id AND pb.is_sold = 0) > 0'))
            ->when($stock === 'sold', fn ($q) => $q->whereRaw('(SELECT COUNT(*) FROM product_barcodes pb WHERE pb.inward_product_id = inward_products.id AND pb.is_sold = 0) = 0'))
            ->when($linked !== null && $linked !== '', fn ($q) => $q->where('i.legacy_voucher_resolved', (int)$linked))
            ->when($fy !== '', fn ($q) => $q->where('i.legacy_financial_year', $fy))
            // Ordered by barcode, newest first. A line carries several barcodes, so
            // it sorts on its highest one; CAST keeps 999 below 1000 rather than
            // letting a string compare put it on top.
            ->orderByRaw('(SELECT MAX(CAST(pb.barcode_number AS UNSIGNED)) FROM product_barcodes pb WHERE pb.inward_product_id = inward_products.id) DESC')
            ->orderByDesc('inward_products.id')
            ->paginate(25)
            ->withQueryString();

        // The barcodes themselves - the whole point of preserving the printed
        // stickers - were never shown. Fetched for the page's lines in one query
        // rather than per row, which at 25 lines x N barcodes would be an N+1.
        $barcodesByLine = DB::table('product_barcodes')
            ->whereIn('inward_product_id', $rows->pluck('id')->all())
            ->orderBy('barcode_number')
            ->get(['inward_product_id', 'barcode_number', 'is_sold'])
            ->groupBy('inward_product_id');

        return view('shop.opening-stock.index', compact(
            'rows', 'totals', 'barcodeStats', 'linkage', 'financialYears',
            'search', 'online', 'stock', 'linked', 'fy', 'barcodesByLine'
        ));
    }

    public function toggleOnline(Request $request, $id)
    {
        $shop = generaleSetting('shop');

        $line = InwardProduct::query()
            ->join('inward_invoices as i', 'inward_products.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shop->id)
            ->where('i.is_opening_stock', 1)
            ->where('inward_products.id', $id)
            ->select('inward_products.*')
            ->first();

        if (!$line) {
            return response()->json(['status' => false, 'message' => __('Opening stock line not found.')], 404);
        }

        $target = !$line->is_online_product;

        try {
            DB::transaction(fn () => $this->applyOnlineState([$line->id], $target, $shop->id));
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'    => true,
            'is_online' => $target,
            'message'   => $target
                ? __('Item is now available on the online store.')
                : __('Item has been removed from the online store.'),
        ]);
    }

    public function bulkToggleOnline(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'state'  => 'required|boolean',
        ]);

        $shop = generaleSetting('shop');
        $target = (bool)$request->boolean('state');

        $ids = InwardProduct::query()
            ->join('inward_invoices as i', 'inward_products.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shop->id)
            ->where('i.is_opening_stock', 1)
            ->whereIn('inward_products.id', $request->input('ids'))
            ->pluck('inward_products.id')
            ->toArray();

        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => __('No matching opening stock lines.')], 404);
        }

        try {
            DB::transaction(fn () => $this->applyOnlineState($ids, $target, $shop->id));
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => true,
            'count'   => count($ids),
            'message' => trans_choice(
                $target ? ':count item published to the online store.|:count items published to the online store.'
                        : ':count item removed from the online store.|:count items removed from the online store.',
                count($ids),
                ['count' => count($ids)]
            ),
        ]);
    }

    /**
     * Flip the flag on the opening stock lines, then reconcile the underlying
     * products. A product is shared by many lines, so it only goes offline once
     * no opening stock line still wants it online.
     */
    protected function applyOnlineState(array $lineIds, bool $target, int $shopId): void
    {
        InwardProduct::whereIn('id', $lineIds)->update(['is_online_product' => $target ? 1 : 0]);

        $productIds = InwardProduct::whereIn('id', $lineIds)
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return;
        }

        if ($target) {
            Product::whereIn('id', $productIds)->update([
                'is_online_product' => 1,
                'is_update_product' => 1,
                'is_active'         => 1,
            ]);

            return;
        }

        // Only take a product offline if nothing else still points at it.
        $stillOnline = InwardProduct::query()
            ->join('inward_invoices as i', 'inward_products.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shopId)
            ->where('i.is_opening_stock', 1)
            ->where('inward_products.is_online_product', 1)
            ->whereIn('inward_products.product_id', $productIds)
            ->pluck('inward_products.product_id')
            ->unique();

        $toDisable = $productIds->diff($stillOnline);

        if ($toDisable->isNotEmpty()) {
            Product::whereIn('id', $toDisable)->update(['is_online_product' => 0]);
        }
    }
}
