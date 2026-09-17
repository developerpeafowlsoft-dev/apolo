<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Detail screens behind the Opening Stock register.
 *
 * The register answers "what is in stock"; these screens answer "where did it
 * come from and how much of it moved". Two views on the same rows:
 *
 *   vendors()  - one line per supplier: bought vs sold vs still on hand
 *   vendor()   - one supplier opened up by year, by item, by challan
 *   insight()  - the detail page behind each summary card on the register
 *
 * GRAIN MATTERS. A printed barcode is one physical unit, so anything counting
 * or valuing stock aggregates product_barcodes. Tax and challan counts live on
 * the inward line and the invoice, so they aggregate inward_products instead -
 * summing them over the barcode join would multiply them by the line quantity.
 * The two are therefore queried separately and merged on the party id.
 *
 * Cost basis is ip.net_purc_rate per barcode, the same basis the opening
 * journal used, so the sum of every vendor's on-hand cost is the figure sitting
 * in the OPENING STOCK ledger account rather than something close to it.
 */
class OpeningStockInsightController extends Controller
{
    /** Sold units that can be traced to an order line, as a share of all sold units. */
    protected const REALISED_NOTE = 'Realised figures cover only sales replayed into orders (FY 2025-26). Sold units come from the legacy sale files and cover every year.';

    // ------------------------------------------------------------------
    // Vendor-wise purchase and sale
    // ------------------------------------------------------------------

    public function vendors(Request $request)
    {
        $shopId = $this->shopId();
        $fy     = trim((string) $request->input('fy'));
        $sort   = (string) $request->input('sort', 'units');
        $dir    = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $rows = $this->vendorRows($shopId, $fy ?: null);

        $sortable = [
            'name', 'units', 'sold_units', 'available_units', 'cost_value', 'sold_cost',
            'onhand_cost', 'mrp_value', 'sell_through', 'realised_net', 'products', 'challans',
        ];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'units';
        }

        $rows = $rows->sortBy(
            fn ($r) => $sort === 'name' ? mb_strtolower((string) $r['name']) : (float) $r[$sort],
            SORT_REGULAR,
            $dir === 'desc'
        )->values();

        $totals = $this->sumRows($rows);
        $financialYears = $this->financialYears($shopId);

        return view('shop.opening-stock.vendors', compact('rows', 'totals', 'financialYears', 'fy', 'sort', 'dir'));
    }

    /**
     * The same table as a spreadsheet. Streamed rather than built in memory -
     * it is only a few hundred lines today, but it is fed by a query whose
     * size follows the supplier master.
     */
    public function vendorsExport(Request $request): StreamedResponse
    {
        $shopId = $this->shopId();
        $fy     = trim((string) $request->input('fy'));
        $rows   = $this->vendorRows($shopId, $fy ?: null)->sortByDesc('units')->values();

        $name = 'vendor-purchase-sale' . ($fy !== '' ? '-' . str_replace('-', '', $fy) : '') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");  // Excel needs the BOM to read UTF-8
            fputcsv($out, [
                'Supplier', 'GSTIN', 'Challans', 'Stock Lines', 'Distinct Items',
                'Units Purchased', 'Purchase Cost', 'Retail Value (MRP)', 'GST on Purchase',
                'Units Sold', 'Sold at Cost', 'Sold at MRP', 'Sell-through %',
                'Units On Hand', 'On Hand at Cost',
                'Traced Order Lines', 'Realised Net Sales', 'Realised Tax', 'Units Returned',
                'First Inward', 'Last Inward',
            ]);
            // Money rounded on the way out: a double summed over thousands of
            // rows prints as 2863621.4300001, which is noise in a spreadsheet.
            $m = fn ($v) => number_format((float) $v, 2, '.', '');

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['name'], $r['gstin'], $r['challans'], $r['line_count'], $r['products'],
                    $r['units'], $m($r['cost_value']), $m($r['mrp_value']), $m($r['gst_value']),
                    $r['sold_units'], $m($r['sold_cost']), $m($r['sold_mrp']), $r['sell_through'],
                    $r['available_units'], $m($r['onhand_cost']),
                    $r['traced_lines'], $m($r['realised_net']), $m($r['realised_tax']), $r['returned_units'],
                    $r['first_date'], $r['last_date'],
                ]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * One supplier opened up. Everything on this page is scoped to that party's
     * opening stock, so the year table, the item table and the challan table all
     * add back to the header figures.
     */
    public function vendor(Request $request, $id)
    {
        $shopId = $this->shopId();
        $partyId = (int) $id;

        $summary = $this->vendorRows($shopId, null, $partyId)->first();
        abort_if(! $summary, 404, __('Supplier has no opening stock.'));

        $party = DB::table('account_masters')
            ->where('id', $partyId)
            ->where('shop_id', $shopId)
            ->first(['id', 'accountName', 'tax_info_gst_no', 'cont_info_mobile1', 'cont_info_email', 'contaddress', 'is_active']);

        // Year by year. legacy_financial_year is the year the challan was
        // inwarded, not the year anything sold - the sale files carry no usable
        // date per barcode, so sold is shown against the year it came in.
        $byYear = $this->barcodeBase($shopId)
            ->where('i.inward_party_code', $partyId)
            ->groupBy('i.legacy_financial_year')
            ->selectRaw('i.legacy_financial_year as fy')
            ->selectRaw('COUNT(pb.id) as units')
            ->selectRaw('SUM(pb.is_sold) as sold_units')
            ->selectRaw('SUM(pb.is_sold = 0) as available_units')
            ->selectRaw('SUM(ip.net_purc_rate) as cost_value')
            ->selectRaw('SUM(pb.mrp) as mrp_value')
            ->selectRaw('SUM(CASE WHEN pb.is_sold = 0 THEN ip.net_purc_rate ELSE 0 END) as onhand_cost')
            ->orderBy('i.legacy_financial_year')
            ->get();

        $yearChallans = $this->lineBase($shopId)
            ->where('i.inward_party_code', $partyId)
            ->groupBy('i.legacy_financial_year')
            ->selectRaw('i.legacy_financial_year as fy, COUNT(DISTINCT i.id) as challans, COUNT(*) as line_count')
            ->pluck('challans', 'fy');

        // Item level, paginated: a large supplier runs to thousands of lines.
        $items = $this->barcodeBase($shopId)
            ->leftJoin('products as p', 'ip.product_id', '=', 'p.id')
            ->leftJoin('hsn_masters as hm', 'ip.hsn_master_id', '=', 'hm.id')
            ->where('i.inward_party_code', $partyId)
            ->when(trim((string) $request->input('item')) !== '', function ($q) use ($request) {
                $term = trim((string) $request->input('item'));
                $q->where(function ($w) use ($term) {
                    $w->where('p.name', 'like', "%{$term}%")->orWhere('p.code', 'like', "%{$term}%");
                });
            })
            ->groupBy('ip.product_id', 'p.name', 'p.code', 'hm.hsn_code')
            ->selectRaw('ip.product_id, p.name as product_name, p.code as product_code, hm.hsn_code')
            ->selectRaw('COUNT(pb.id) as units')
            ->selectRaw('SUM(pb.is_sold) as sold_units')
            ->selectRaw('SUM(pb.is_sold = 0) as available_units')
            ->selectRaw('SUM(ip.net_purc_rate) as cost_value')
            ->selectRaw('SUM(pb.mrp) as mrp_value')
            ->selectRaw('AVG(ip.net_purc_rate) as avg_cost')
            ->selectRaw('AVG(pb.mrp) as avg_mrp')
            ->orderByRaw('COUNT(pb.id) DESC')
            ->paginate(40)
            ->withQueryString();

        // Challans, newest first. Capped: the point is to recognise the run of
        // documents, not to reprint the purchase register.
        $challans = $this->lineBase($shopId)
            ->where('i.inward_party_code', $partyId)
            ->groupBy('i.id', 'i.inward_challan_no', 'i.inward_voucher_no', 'i.inward_date', 'i.legacy_financial_year', 'i.legacy_voucher_resolved', 'i.legacy_source')
            ->selectRaw('i.id, i.inward_challan_no, i.inward_voucher_no, i.inward_date, i.legacy_financial_year, i.legacy_voucher_resolved, i.legacy_source')
            ->selectRaw('COUNT(*) as line_count, SUM(ip.quantity) as units')
            ->selectRaw('COALESCE(SUM(ip.net_purc_price), 0) as cost_value')
            ->selectRaw('COALESCE(SUM(COALESCE(ip.sgst_amount, 0) + COALESCE(ip.cgst_amount, 0) + COALESCE(ip.igst_amount, 0)), 0) as gst_value')
            ->orderByDesc('i.inward_date')
            ->limit(200)
            ->get();

        $challanTotal = (int) $this->lineBase($shopId)->where('i.inward_party_code', $partyId)->distinct()->count('i.id');

        // Sold units that reached an order line, so the supplier's stock can be
        // followed all the way to a bill. Only FY 2025-26 was replayed, so this
        // is a window onto the sales, not the whole of them.
        $tracedSales = $this->salesBase($shopId)
            ->where('i.inward_party_code', $partyId)
            ->leftJoin('products as p2', 'ip.product_id', '=', 'p2.id')
            ->orderByDesc('o.created_at')
            ->limit(100)
            ->get([
                'op.barcode_number', 'op.quantity', 'op.price', 'op.mrp', 'op.tax_amount',
                'op.discount_amount', 'o.order_code', 'o.created_at', 'o.order_status',
                'p2.name as product_name',
            ]);

        return view('shop.opening-stock.vendor', compact(
            'party', 'summary', 'byYear', 'yearChallans', 'items', 'challans', 'challanTotal', 'tracedSales'
        ) + ['itemSearch' => trim((string) $request->input('item'))]);
    }

    // ------------------------------------------------------------------
    // The page behind a summary card
    // ------------------------------------------------------------------

    public function insight(Request $request, string $metric)
    {
        $shopId = $this->shopId();
        $defs = $this->metricDefs();
        abort_if(! isset($defs[$metric]), 404);

        $def = $defs[$metric];
        $fy  = trim((string) $request->input('fy'));

        // Every card is shown, so the page doubles as a way of moving between
        // them without going back to the register first. All nine come back in
        // two queries rather than nine - one pass per grain.
        $siblings = $this->metricStrip($shopId, $fy ?: null);
        $headline = $siblings[$metric]['value'];

        $breakdowns = [];
        foreach ($def['dimensions'] as $dim) {
            $breakdowns[$dim] = $this->breakdown($shopId, $def, $dim, $fy ?: null);
        }

        $financialYears = $this->financialYears($shopId);
        $span = $financialYears
            ? reset($financialYears) . ' → ' . end($financialYears)
            : __('no data');

        return view('shop.opening-stock.insight', compact(
            'metric', 'def', 'headline', 'siblings', 'breakdowns', 'financialYears', 'fy', 'span'
        ));
    }

    // ------------------------------------------------------------------
    // Query building blocks
    // ------------------------------------------------------------------

    protected function shopId(): int
    {
        return (int) generaleSetting('shop')->id;
    }

    /** One row per printed barcode: the grain for counting and valuing stock. */
    protected function barcodeBase(int $shopId, ?string $fy = null)
    {
        $q = DB::table('product_barcodes as pb')
            ->join('inward_products as ip', 'ip.id', '=', 'pb.inward_product_id')
            ->join('inward_invoices as i', 'ip.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shopId)
            ->where('i.is_opening_stock', 1);

        return $fy ? $q->where('i.legacy_financial_year', $fy) : $q;
    }

    /** One row per inward line: the grain for tax, challan counts and line counts. */
    protected function lineBase(int $shopId, ?string $fy = null)
    {
        $q = DB::table('inward_products as ip')
            ->join('inward_invoices as i', 'ip.inward_invoice_id', '=', 'i.id')
            ->where('i.shop_id', $shopId)
            ->where('i.is_opening_stock', 1);

        return $fy ? $q->where('i.legacy_financial_year', $fy) : $q;
    }

    /**
     * Sold barcodes that reached an order line. The join is on the printed
     * number because order_products.inward_product_id was never populated by
     * the replay. Quantity carries its own sign - a return is a -1 line - so
     * summing it nets returns off without filtering on order_status, which
     * would double-count: negative lines sit under both statuses.
     */
    protected function salesBase(int $shopId, ?string $fy = null)
    {
        $q = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('product_barcodes as pb', 'pb.barcode_number', '=', 'op.barcode_number')
            ->join('inward_products as ip', 'ip.id', '=', 'pb.inward_product_id')
            ->join('inward_invoices as i', 'ip.inward_invoice_id', '=', 'i.id')
            ->where('o.shop_id', $shopId)
            ->where('i.shop_id', $shopId)
            ->where('i.is_opening_stock', 1);

        return $fy ? $q->where('i.legacy_financial_year', $fy) : $q;
    }

    protected function financialYears(int $shopId): array
    {
        return $this->lineBase($shopId)
            ->whereNotNull('i.legacy_financial_year')
            ->distinct()
            ->orderBy('i.legacy_financial_year')
            ->pluck('i.legacy_financial_year')
            ->all();
    }

    /**
     * Supplier rows, assembled from the three grains. Optionally narrowed to one
     * party so the detail page and the list cannot disagree about a total.
     */
    protected function vendorRows(int $shopId, ?string $fy = null, ?int $partyId = null)
    {
        $stock = $this->barcodeBase($shopId, $fy)
            ->when($partyId, fn ($q) => $q->where('i.inward_party_code', $partyId))
            ->groupBy('i.inward_party_code')
            ->selectRaw('i.inward_party_code as party_id')
            ->selectRaw('COUNT(pb.id) as units')
            ->selectRaw('SUM(pb.is_sold) as sold_units')
            ->selectRaw('SUM(pb.is_sold = 0) as available_units')
            ->selectRaw('SUM(ip.net_purc_rate) as cost_value')
            ->selectRaw('SUM(pb.mrp) as mrp_value')
            ->selectRaw('SUM(CASE WHEN pb.is_sold = 1 THEN ip.net_purc_rate ELSE 0 END) as sold_cost')
            ->selectRaw('SUM(CASE WHEN pb.is_sold = 1 THEN pb.mrp ELSE 0 END) as sold_mrp')
            ->selectRaw('SUM(CASE WHEN pb.is_sold = 0 THEN ip.net_purc_rate ELSE 0 END) as onhand_cost')
            ->selectRaw('SUM(CASE WHEN pb.is_sold = 0 THEN pb.mrp ELSE 0 END) as onhand_mrp')
            ->get()
            ->keyBy('party_id');

        $docs = $this->lineBase($shopId, $fy)
            ->when($partyId, fn ($q) => $q->where('i.inward_party_code', $partyId))
            ->groupBy('i.inward_party_code')
            ->selectRaw('i.inward_party_code as party_id')
            ->selectRaw('COUNT(DISTINCT i.id) as challans')
            ->selectRaw('COUNT(*) as line_count')
            ->selectRaw('COUNT(DISTINCT ip.product_id) as products')
            ->selectRaw('COALESCE(SUM(COALESCE(ip.sgst_amount, 0) + COALESCE(ip.cgst_amount, 0) + COALESCE(ip.igst_amount, 0)), 0) as gst_value')
            ->selectRaw('SUM(CASE WHEN i.legacy_voucher_resolved = 1 THEN 1 ELSE 0 END) as linked_lines')
            ->selectRaw('MIN(i.inward_date) as first_date')
            ->selectRaw('MAX(i.inward_date) as last_date')
            ->get()
            ->keyBy('party_id');

        $sales = $this->salesBase($shopId, $fy)
            ->when($partyId, fn ($q) => $q->where('i.inward_party_code', $partyId))
            ->groupBy('i.inward_party_code')
            ->selectRaw('i.inward_party_code as party_id')
            ->selectRaw('COUNT(*) as traced_lines')
            ->selectRaw('SUM(op.quantity) as traced_units')
            ->selectRaw('SUM(CASE WHEN op.quantity < 0 THEN -op.quantity ELSE 0 END) as returned_units')
            ->selectRaw('SUM(op.price * op.quantity) as realised_net')
            ->selectRaw('SUM(op.tax_amount * op.quantity) as realised_tax')
            ->selectRaw('SUM(op.discount_amount * op.quantity) as realised_discount')
            ->get()
            ->keyBy('party_id');

        $names = DB::table('account_masters')
            ->whereIn('id', $stock->keys()->all() ?: [0])
            ->get(['id', 'accountName', 'tax_info_gst_no', 'cont_info_mobile1', 'is_active'])
            ->keyBy('id');

        return $stock->map(function ($s) use ($docs, $sales, $names) {
            $pid  = (int) $s->party_id;
            $d    = $docs[$pid] ?? null;
            $x    = $sales[$pid] ?? null;
            $acc  = $names[$pid] ?? null;
            $units = (int) $s->units;

            return [
                'party_id'          => $pid,
                'name'              => $acc->accountName ?? __('(unnamed supplier)'),
                'gstin'             => $acc->tax_info_gst_no ?? null,
                'mobile'            => $acc->cont_info_mobile1 ?? null,
                'is_active'         => (int) ($acc->is_active ?? 0),
                'challans'          => (int) ($d->challans ?? 0),
                'line_count'        => (int) ($d->line_count ?? 0),
                'products'          => (int) ($d->products ?? 0),
                'linked_lines'      => (int) ($d->linked_lines ?? 0),
                'gst_value'         => (float) ($d->gst_value ?? 0),
                'first_date'        => $d->first_date ?? null,
                'last_date'         => $d->last_date ?? null,
                'units'             => $units,
                'sold_units'        => (int) $s->sold_units,
                'available_units'   => (int) $s->available_units,
                'cost_value'        => (float) $s->cost_value,
                'mrp_value'         => (float) $s->mrp_value,
                'sold_cost'         => (float) $s->sold_cost,
                'sold_mrp'          => (float) $s->sold_mrp,
                'onhand_cost'       => (float) $s->onhand_cost,
                'onhand_mrp'        => (float) $s->onhand_mrp,
                'sell_through'      => $units > 0 ? round($s->sold_units / $units * 100, 1) : 0.0,
                'traced_lines'      => (int) ($x->traced_lines ?? 0),
                'traced_units'      => (int) ($x->traced_units ?? 0),
                'returned_units'    => (int) ($x->returned_units ?? 0),
                'realised_net'      => (float) ($x->realised_net ?? 0),
                'realised_tax'      => (float) ($x->realised_tax ?? 0),
                'realised_discount' => (float) ($x->realised_discount ?? 0),
            ];
        })->values();
    }

    protected function sumRows($rows): array
    {
        $keys = [
            'challans', 'line_count', 'products', 'units', 'sold_units', 'available_units',
            'cost_value', 'mrp_value', 'gst_value', 'sold_cost', 'sold_mrp', 'onhand_cost',
            'onhand_mrp', 'traced_lines', 'traced_units', 'returned_units', 'realised_net',
            'realised_tax', 'realised_discount',
        ];

        $t = ['vendors' => $rows->count()];
        foreach ($keys as $k) {
            $t[$k] = $rows->sum($k);
        }
        $t['sell_through'] = $t['units'] > 0 ? round($t['sold_units'] / $t['units'] * 100, 1) : 0.0;

        return $t;
    }

    // ------------------------------------------------------------------
    // Card metrics
    // ------------------------------------------------------------------

    /**
     * The nine figures on the register's summary strip, each with the grain it
     * is measured at and the filter that defines it. Kept in one place so a card
     * and its detail page cannot drift apart.
     */
    protected function metricDefs(): array
    {
        $dims = ['fy', 'vendor', 'hsn', 'challan'];

        return [
            'units' => [
                'label'   => __('Total Units'),
                'sub'     => __('barcodes imported'),
                'grain'   => 'barcode',
                'measure' => 'COUNT(pb.id)',
                'filter'  => null,
                'money'   => false,
                'icon'    => 'fa-barcode',
                'blurb'   => __('Every printed sticker carried over from the desktop system, across all ten years and de-duplicated. One barcode is one physical piece.'),
                'sql'     => 'COUNT(*) FROM product_barcodes',
                'dimensions' => $dims,
            ],
            'available' => [
                'label'   => __('Available'),
                'sub'     => __('sellable at POS'),
                'grain'   => 'barcode',
                'measure' => 'COUNT(pb.id)',
                'filter'  => 'pb.is_sold = 0',
                'money'   => false,
                'icon'    => 'fa-box-open',
                'blurb'   => __('Barcodes that never appeared in any sale file, so they are still on the shelf and can be scanned at the counter today.'),
                'sql'     => 'COUNT(*) FROM product_barcodes WHERE is_sold = 0',
                'dimensions' => $dims,
            ],
            'sold' => [
                'label'   => __('Sold'),
                'sub'     => __('consumed by invoices'),
                'grain'   => 'barcode',
                'measure' => 'COUNT(pb.id)',
                'filter'  => 'pb.is_sold = 1',
                'money'   => false,
                'icon'    => 'fa-cart-shopping',
                'blurb'   => __('Barcodes matched against the sale files of 2017-18 through 2025-26. Where a barcode sold and came back, the last event on it decided the flag.'),
                'sql'     => 'COUNT(*) FROM product_barcodes WHERE is_sold = 1',
                'dimensions' => $dims,
            ],
            'lines' => [
                'label'   => __('Stock Lines'),
                'sub'     => __('distinct SKU / price rows'),
                'grain'   => 'line',
                'measure' => 'COUNT(*)',
                'filter'  => null,
                'money'   => false,
                'icon'    => 'fa-layer-group',
                'blurb'   => __('One row per item at one purchase rate on one challan. The same shirt bought twice at two different rates is two lines; bought twenty times at one rate on one challan it is a single line carrying twenty barcodes.'),
                'sql'     => 'COUNT(*) FROM inward_products',
                'dimensions' => ['fy', 'vendor', 'hsn', 'tax', 'challan'],
            ],
            'cost' => [
                'label'   => __('Value at Cost'),
                'sub'     => __('net purchase cost'),
                'grain'   => 'barcode',
                'measure' => 'SUM(ip.net_purc_rate)',
                'filter'  => null,
                'money'   => true,
                'icon'    => 'fa-indian-rupee-sign',
                'blurb'   => __('Net purchase rate per barcode, summed. The unsold part of this figure is what the opening journal debited to the OPENING STOCK account.'),
                'note'    => __('The register card adds the line totals instead of the per-barcode rates and lands five paise higher, at ₹ 3,95,14,889.30. Both are the same money; the difference is where the rounding falls. This page uses the per-barcode basis because that is the basis the opening journal was posted on.'),
                'sql'     => 'SUM(inward_products.net_purc_rate) per barcode',
                'dimensions' => $dims,
            ],
            'mrp' => [
                'label'   => __('Value at MRP'),
                'sub'     => __('retail value'),
                'grain'   => 'barcode',
                'measure' => 'SUM(pb.mrp)',
                'filter'  => null,
                'money'   => true,
                'icon'    => 'fa-tags',
                'blurb'   => __('What the same barcodes are worth at the printed label price. The gap against cost is the gross margin the stock is carrying.'),
                'sql'     => 'SUM(product_barcodes.mrp)',
                'dimensions' => $dims,
            ],
            'linked' => [
                'label'   => __('Challan Linked'),
                'sub'     => __('units with a real inward voucher'),
                'grain'   => 'barcode',
                'measure' => 'COUNT(pb.id)',
                'filter'  => 'i.legacy_voucher_resolved = 1',
                'money'   => false,
                'icon'    => 'fa-link',
                'blurb'   => __('Units whose stock row was matched to an inward challan in the Voucher Detail Wise INWARD export, so they carry a document number, a GST split and a sales rate.'),
                'sql'     => 'COUNT(*) WHERE inward_invoices.legacy_voucher_resolved = 1',
                'dimensions' => ['fy', 'vendor', 'hsn'],
            ],
            'snapshot' => [
                'label'   => __('Snapshot Only'),
                'sub'     => __('no challan in the exports'),
                'grain'   => 'barcode',
                'measure' => 'COUNT(pb.id)',
                'filter'  => 'i.legacy_voucher_resolved = 0',
                'money'   => false,
                'icon'    => 'fa-file-circle-question',
                'blurb'   => __('Units that exist in the barcode sheet but whose inward challan was not in the exports. Quantity, cost and MRP are trustworthy; the GST split and the document number are not available.'),
                'sql'     => 'COUNT(*) WHERE inward_invoices.legacy_voucher_resolved = 0',
                'dimensions' => ['fy', 'vendor', 'hsn'],
            ],
            'gst' => [
                'label'   => __('GST on Purchase'),
                'sub'     => __('SGST + CGST + IGST from challans'),
                'grain'   => 'line',
                'measure' => 'SUM(COALESCE(ip.sgst_amount, 0) + COALESCE(ip.cgst_amount, 0) + COALESCE(ip.igst_amount, 0))',
                'filter'  => null,
                'money'   => true,
                'icon'    => 'fa-receipt',
                'blurb'   => __('Input tax recorded on the inward lines that came from a challan. It is measured per line, not per barcode - spreading it over units would multiply it by the line quantity.'),
                'sql'     => 'SUM(sgst_amount + cgst_amount + igst_amount) FROM inward_products',
                'dimensions' => ['fy', 'vendor', 'hsn', 'tax'],
            ],
        ];
    }

    /**
     * All nine card figures, in one pass per grain.
     *
     * Asked one at a time this is nine full sweeps of 166,769 barcodes for a
     * page that only needs two. Each metric's filter is folded into its own
     * aggregate instead of a WHERE, so metrics that disagree about which rows
     * they want still ride the same scan.
     */
    protected function metricStrip(int $shopId, ?string $fy): array
    {
        $defs = $this->metricDefs();
        $out = [];

        foreach (['barcode', 'line'] as $grain) {
            $metrics = array_filter($defs, fn ($d) => $d['grain'] === $grain);

            if (! $metrics) {
                continue;
            }

            $q = $grain === 'line' ? $this->lineBase($shopId, $fy) : $this->barcodeBase($shopId, $fy);

            foreach ($metrics as $key => $d) {
                $q->selectRaw($this->valueExpr($d) . ' as `' . $key . '`');
            }

            $row = (array) $q->first();

            foreach ($metrics as $key => $d) {
                $out[$key] = [
                    'label' => $d['label'],
                    'value' => (float) ($row[$key] ?? 0),
                    'money' => $d['money'],
                ];
            }
        }

        // Back into the order the cards are declared in, so the strip on the
        // detail page reads the same way round as the strip on the register.
        return array_replace(array_intersect_key($defs, $out), $out);
    }

    /**
     * The dimensions a metric can be cut by. Each carries the expression to
     * group on and the joins that expression needs, so the breakdown query only
     * pays for the tables it actually reads.
     */
    protected function dimensionDefs(): array
    {
        return [
            'fy' => [
                'label'  => __('By inward financial year'),
                'head'   => __('Financial Year'),
                'expr'   => 'i.legacy_financial_year',
                'join'   => null,
                'note'   => __('The Indian financial year of the inward date on the row, not the year folder the export was read from — a challan dated March 2016 lands in 2015-2016 either way.'),
            ],
            'vendor' => [
                'label'  => __('By supplier'),
                'head'   => __('Supplier'),
                'expr'   => 'am.accountName',
                'key'    => 'i.inward_party_code',
                'join'   => ['account_masters as am', 'i.inward_party_code', '=', 'am.id'],
                'note'   => __('Top suppliers by this measure. The full list is on the vendor report.'),
                'limit'  => 25,
            ],
            'hsn' => [
                'label'  => __('By HSN code'),
                'head'   => __('HSN'),
                'expr'   => 'hm.hsn_code',
                'join'   => ['hsn_masters as hm', 'ip.hsn_master_id', '=', 'hm.id'],
                'note'   => __('The tax classification the item was inwarded under.'),
                'limit'  => 25,
            ],
            'tax' => [
                'label'  => __('By tax rate on the challan'),
                'head'   => __('Tax'),
                'expr'   => 'ip.tax_name',
                'join'   => null,
                'note'   => __('Blank where the line came from a snapshot with no challan behind it.'),
            ],
            'challan' => [
                'label'  => __('By document backing'),
                'head'   => __('Backing'),
                'expr'   => "CASE WHEN i.legacy_voucher_resolved = 1 THEN 'Linked to challan' ELSE 'Snapshot only' END",
                'join'   => null,
                'note'   => __('Whether the row could be matched to an inward challan in the exports.'),
            ],
        ];
    }

    protected function breakdown(int $shopId, array $def, string $dim, ?string $fy): array
    {
        $d = $this->dimensionDefs()[$dim];

        $value    = $this->valueExpr($def);
        $universe = $this->unfiltered($def);

        $q = $def['grain'] === 'line' ? $this->lineBase($shopId, $fy) : $this->barcodeBase($shopId, $fy);

        if ($d['join']) {
            $q->leftJoin($d['join'][0], $d['join'][1], $d['join'][2], $d['join'][3]);
        }

        // Grouped on the id where the dimension has one, so two suppliers
        // sharing a name stay apart and the row has something to link to.
        $groupKey = $d['key'] ?? null;

        $rows = $q
            ->groupByRaw($groupKey ? $groupKey . ', ' . $d['expr'] : $d['expr'])
            ->selectRaw($d['expr'] . ' as label')
            ->selectRaw(($groupKey ?: 'NULL') . ' as ref')
            ->selectRaw($value . ' as value')
            // The denominator: the same measure with the metric's filter lifted,
            // so "40% of what came in from this supplier has sold" reads straight
            // off the row instead of needing a second page.
            ->selectRaw($universe . ' as universe')
            ->orderByRaw($value . ' DESC')
            ->limit($d['limit'] ?? 60)
            ->get();

        return [
            'meta'  => $d,
            'rows'  => $rows,
            'total' => (float) $rows->sum('value'),
            'max'   => (float) ($rows->max('value') ?: 1),
            'dim'   => $dim,
        ];
    }

    /**
     * The measure rewritten so the metric's filter lives inside the aggregate
     * rather than in a WHERE. Both the filtered value and the unfiltered
     * universe then come back from one grouped pass over the same rows.
     */
    protected function valueExpr(array $def): string
    {
        if (! $def['filter']) {
            return $def['measure'];
        }

        if (str_starts_with($def['measure'], 'COUNT')) {
            return 'SUM(CASE WHEN ' . $def['filter'] . ' THEN 1 ELSE 0 END)';
        }

        $inner = substr($def['measure'], 4, -1);   // SUM( ... )

        return 'SUM(CASE WHEN ' . $def['filter'] . ' THEN ' . $inner . ' ELSE 0 END)';
    }

    /** The metric's measure with its filter lifted, for the share column. */
    protected function unfiltered(array $def): string
    {
        return str_starts_with($def['measure'], 'COUNT') ? 'COUNT(*)' : $def['measure'];
    }
}
