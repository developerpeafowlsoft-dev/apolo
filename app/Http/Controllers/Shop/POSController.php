<?php

namespace App\Http\Controllers\Shop;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\PosApplyCouponRequest;
use App\Http\Requests\PosCartRequest;
use App\Http\Requests\POSCheckoutRequest;
use App\Services\POS\POSCheckoutService;
use App\Http\Resources\POSOrderResource;
use App\Http\Resources\PosCartProductResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Coupon;
use App\Models\CounterMaster;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PosCart;
use App\Models\PosCartProduct;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PosCartRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\VatTaxRepository;
use App\Rules\EmailRule;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class POSController extends Controller
{
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        $categories = $rootShop->categories()->active()->get();
        $brands = $rootShop->brands()->isActive()->get();

        $generaleSetting = generaleSetting('setting');
        $defaultCurrency = generaleSetting('defaultCurrency');
        $currency = $defaultCurrency?->symbol ?: ($generaleSetting?->currency ?: '₹');
        $currencyPosition = $generaleSetting?->currency_position ?: 'prefix';

        $customers = Customer::whereHas('user', function ($query) {
            return $query->where('deleted_at', null);
        })->get();

        $salesmans = \App\Models\Salesman::where('shop_id', $shop?->id)->where('is_active', 1)->get();

        $cashiers = \App\Models\User::where(function($q) use ($shop) {
                if ($shop) {
                    $q->where('shop_id', $shop->id);
                } else {
                    $q->whereNull('shop_id');
                }
            })
            ->whereHas('roles', function($r) {
                $r->whereIn('name', ['shop', 'admin', 'root', 'staff', 'cashier']);
            })
            ->get(['id', 'name', 'email']);

        if ($cashiers->isEmpty()) {
            $cashiers = \App\Models\User::where('id', auth()->id())->get(['id', 'name', 'email']);
        }

        $counters = CounterMaster::where('shop_id', $shop?->id)
            ->where('is_active', true)
            ->orderBy('counter_name')
            ->get();

        return view('shop.pos.index', compact('categories', 'brands', 'customers', 'currency', 'currencyPosition', 'salesmans', 'counters', 'cashiers'));
    }

    /**
     * JSON endpoint — returns active counters for the current shop.
     * Used by the POS counter-selection modal.
     */
    public function getCounters()
    {
        $shop = generaleSetting('shop');
        $counters = CounterMaster::where('shop_id', $shop?->id)
            ->where('is_active', true)
            ->orderBy('counter_name')
            ->get(['id', 'code', 'counter_name', 'counter_short_name', 'floor', 'voucher_prefix']);
        return response()->json($counters);
    }

    public function sales()
    {
        $shop = generaleSetting('shop');

        $orders = OrderRepository::query()->withoutGlobalScopes()->where('shop_id', $shop->id)->where('pos_order', true)->paginate(20);

        return view('shop.pos.sales', compact('orders'));
    }

    public function draft()
    {
        $shop = generaleSetting('shop');

        // Delete all PosCart records where products count is zero
        PosCartRepository::query()->where('is_draft', true)->whereDoesntHave('products')?->delete();

        $posCarts = PosCartRepository::query()->where('shop_id', $shop->id)->where('is_draft', true)->orderBy('created_at', 'desc')->get();

        return view('shop.pos.draft', compact('posCarts'));
    }

    public function draftDelete(PosCart $posCart)
    {
        $posCart->products()->sync([]);
        $posCart->delete();

        return back()->withSuccess(__('Draft deleted successfully'));
    }

    public function invoice($orderId)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['products', 'customer.user', 'vatTaxes', 'salesman', 'cashier', 'counter'])
            ->where('uuid', $orderId)
            ->orWhere('id', $orderId)
            ->firstOrFail();

        $setting = generaleSetting('setting');
        $printerType = $setting?->pos_printer_type ?? 'thermal';

        if ($printerType === 'normal' || $printerType === 'a4') {
            return view('shop.pos.a4_invoice_layout', compact('order'));
        }

        return view('shop.pos.thermal_print_layout', compact('order'));
    }

    public function thermalPreview($orderId)
    {
        return $this->invoice($orderId);
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $shop = generaleSetting('shop');

        $postCart = PosCartRepository::query()->where('shop_id', $shop->id)->where('name', $request->name)->first();

        if (! $postCart) {
            return $this->json(__('Sorry shop cart is empty'), [], 422);
        }
        if ($postCart->products->count() == 0) {
            return $this->json(__('Please select a products.'), [], 422);
        }

        $order = PosCartRepository::storeOrder($postCart, $request);

        $message = __('Sale created successfully');
        $request->order_type == 'draft' ? $message = __('Sale draft created successfully') : '';

        $invoiceUrl = null;
        if (is_object($order)) {
            $invoiceUrl = route('shop.pos.invoice', $order->id);
        }

        return $this->json($message, [
            'invoice_url' => $invoiceUrl,
        ], 200);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:200',
            'last_name' => 'nullable|string|max:200',
            'phone' => 'required|string|unique:users,phone|digits_between:6,25',
            'email' => ['nullable', 'email', 'max:200', new EmailRule],
        ]);

        $request['is_active'] = 1;

        $user = UserRepository::registerNewUser($request);

        $user->assignRole(Roles::CUSTOMER->value);

        $customer = CustomerRepository::storeByRequest($user);

        return $this->json(__('Created Successfully'), [
            'user' => (object) [
                'id' => $customer->id,
                'name' => Str::limit($user->fullName, 30, '...') . '-(' . $user->phone . ')',
            ],
        ], 200);
    }

    public function getProduct(Request $request)
    {
        $brand = $request->brand;
        $category = $request->category;
        $search = $request->search;

        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 40;
        $skip = ($page * $perPage) - $perPage;

        $generaleSetting = generaleSetting('setting');

        $shop = generaleSetting('shop');

        $products = $shop->products()
            ->whereHas('barcodes', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                      ->where('is_sold', 0);
            })
            ->when($brand, function ($query) use ($brand) {
                return $query->where('brand_id', $brand);
            })->when($category, function ($product) use ($category) {
                return $product->whereHas('categories', function ($query) use ($category) {
                    return $query->where('category_id', $category);
                });
            })->when($search, function ($query) use ($search, $shop) {
                return $query->where(function ($q) use ($search, $shop) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('barcodes', function ($bq) use ($search, $shop) {
                          $bq->where('shop_id', $shop->id)
                             ->where('barcode_number', $search);
                      });
                });
            })->isActive();
        $total = $products->count();
        $products = $products->skip($skip)->take($perPage)->get();

        return $this->json('Products', [
            'total' => $total,
            'currency' => generaleSetting('defaultCurrency')?->symbol ?? ($generaleSetting?->currency ?? '₹'),
            'currency_position' => $generaleSetting?->currency_position ?? 'prefix',
            'products' => ProductResource::collection($products),
        ]);
    }

    public function addToCart(PosCartRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        if ($product->available_stock < $request->quantity) {
            return $this->json(__('Sorry! product cart quantity is limited. No more stock'), [], 422);
        }

        PosCartRepository::storeByRequest($request, $product);

        return $this->json(__('Product added successfully'), [], 200);
    }

    public function getCart(Request $request)
    {
        $postCart = PosCartRepository::getLatestCart($request);

        $vatTaxes = VatTaxRepository::getActiveVatTaxes();
        $subtotal = $postCart?->subtotal ?? 0;

        $allVatTaxes = [];
        $totalTaxAmount = 0;

        foreach ($vatTaxes ?? [] as $vatTax) {
            if ($vatTax?->name && $vatTax?->percentage > 0 && $subtotal > 0) {
                $taxAmount = round($subtotal * ($vatTax->percentage / 100), 2);

                $allVatTaxes[] = (object) [
                    'name' => $vatTax->name,
                    'percentage' => $vatTax->percentage,
                    'amount' => $taxAmount,
                ];

                $totalTaxAmount += $taxAmount;
            }
        }
        $total = $postCart?->total ?? 0;

        return $this->json('pos cart', [
            'subtotal' => $postCart?->subtotal ?? 0,
            'discount' => $postCart?->discount ?? 0,
            'total_tax_amount' => $totalTaxAmount,
            'total' => (float) round($totalTaxAmount + $total, 2),
            'taxes' => $allVatTaxes,
            'user' => $postCart?->user ? UserResource::make($postCart->user) : null,
            'name' => $postCart?->name ?? null,
            'coupon_code' => $postCart?->coupon?->code ?? null,
            'products' => $postCart?->products ? PosCartProductResource::collection($postCart?->products) : [],
        ]);
    }

    public function updateCart(PosCartRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        if ($product->available_stock < $request->quantity) {
            return $this->json(__('Sorry! product cart quantity is limited. No more stock'), [], 422);
        }

        $postCartProduct = PosCartProduct::find($request->pos_cart_id);
        if (! $postCartProduct) {
            return $this->json(__('Sorry this product is not in cart'), [], 422);
        }

        PosCartRepository::updateByRequest($request, $postCartProduct);
    }

    public function removeCart(Request $request)
    {
        $shop = generaleSetting('shop');

        $postCart = PosCartRepository::query()->where('shop_id', $shop->id)->where('name', $request->name)->first();

        PosCartRepository::destroyProduct($request, $postCart);

        return $this->json(__('Product deleted successfully'), [], 200);
    }

    public function applyCoupon(PosApplyCouponRequest $request)
    {
        $code = $request->coupon_code;
        $shop = generaleSetting('shop');

        $coupon = Coupon::where(function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->orWhereHas('shops', function ($q) use ($shop) {
                $q->where('id', $shop->id);
            });
        })->where('code', $code)->active()->isValid()->first();

        if (! $coupon) {
            return $this->json(__('Invalid coupon code'), [], 422);
        }

        $postCart = PosCartRepository::query()->where('shop_id', $shop->id)->where('name', $request->name)->first();

        if ($postCart) {
            $postCart = PosCartRepository::applyCoupon($request, $coupon, $postCart);

            if ($postCart->discount > 0) {
                return $this->json(__('Coupon applied'), [], 200);
            } else {
                return $this->json(__('Coupon not applied'), [], 422);
            }
        }

        return $this->json(__('Coupon not applied'), [], 422);
    }

    public function removeCoupon(Request $request)
    {
        $shop = generaleSetting('shop');

        $postCart = PosCartRepository::query()->where('shop_id', $shop->id)->where('name', $request->name)->first();

        if ($postCart) {
            $postCart = PosCartRepository::removeCoupon($postCart);

            return $this->json(__('Coupon removed'), [], 200);
        }

        return $this->json(__('Coupon not found'), [], 422);
    }

    public function resolveBarcode($barcode)
    {
        $shop = generaleSetting('shop');
        $barcodeRecord = \App\Models\ProductBarcode::with([
            'product',
            'inwardProduct.designMaster',
            'inwardProduct.hsnMaster',
            'inwardProduct.vatTax',
            'inwardProduct.colors',
            'inwardProduct.sizes'
        ])->where('barcode_number', $barcode)
          ->where('shop_id', $shop?->id)
          ->first();

        if (!$barcodeRecord) {
            return response()->json(['error' => __('Unsold barcode not found')], 404);
        }

        if ($barcodeRecord->is_sold == 1) {
            return response()->json(['error' => __('This barcode has already been sold or used.')], 422);
        }

        if ($barcodeRecord->is_printed == 0) {
            return response()->json(['error' => __('Barcode has not been printed yet. Please print the barcode before selling.')], 422);
        }

        $inwardProduct = $barcodeRecord->inwardProduct;
        $product = $barcodeRecord->product;

        $rate = (float)($barcodeRecord->mrp ?? $inwardProduct->price ?? 0);
        $hsnMaster = $inwardProduct->hsnMaster ?? $product->hsnMaster ?? null;

        $taxPercentage = 0;
        if ($hsnMaster) {
            $taxPercentage = (float)$hsnMaster->getTaxForPrice($rate);
        } else {
            $taxPercentage = (float)($inwardProduct->vatTax?->percentage ?? $product->vatTax?->percentage ?? 0);
        }

        $subHsnList = [];
        if ($hsnMaster && $hsnMaster->subHsn) {
            foreach ($hsnMaster->subHsn as $sub) {
                $subVat = \App\Models\VatTax::find($sub->vat_tax_id);
                $subHsnList[] = [
                    'from_sales_rate' => (float)($sub->from_sales_rate ?? 0),
                    'to_sales_rate' => (float)($sub->to_sales_rate ?? 0),
                    'tax_percentage' => (float)($subVat?->percentage ?? 0)
                ];
            }
        }

        return response()->json([
            'barcode' => $barcodeRecord->barcode_number,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'hsn_code' => $hsnMaster?->hsn_code ?? '',
            'design_no' => $inwardProduct->designMaster?->design_number ?? '',
            'colors' => $inwardProduct->colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'sizes' => $inwardProduct->sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            'rate' => $rate,
            'tax_percentage' => $taxPercentage,
            'sub_hsn' => $subHsnList
        ]);
    }

    public function searchProducts(Request $request)
    {
        $qStr = $request->query('query');
        if (strlen($qStr) < 2) {
            return response()->json([]);
        }

        $shop = generaleSetting('shop');
        $exclude = array_filter(explode(',', $request->query('exclude_barcodes') ?? ''));

        // Subquery to find distinct product_ids with a single unsold printed barcode ID
        $barcodeIds = \App\Models\ProductBarcode::where('shop_id', $shop?->id)
            ->where('is_sold', 0)
            ->where('is_printed', 1)
            ->when(!empty($exclude), function ($query) use ($exclude) {
                $query->whereNotIn('barcode_number', $exclude);
            })
            ->where(function ($query) use ($qStr) {
                $query->where('barcode_number', 'like', "%{$qStr}%")
                      ->orWhereHas('product', function($q) use ($qStr) {
                          $q->where('name', 'like', "%{$qStr}%");
                      })
                      ->orWhereHas('inwardProduct.designMaster', function($q) use ($qStr) {
                          $q->where('design_number', 'like', "%{$qStr}%");
                      });
            })
            ->selectRaw('MIN(id) as id')
            ->groupBy('product_id')
            ->limit(25)
            ->pluck('id');

        $barcodes = \App\Models\ProductBarcode::with(['product', 'inwardProduct.designMaster'])
            ->whereIn('id', $barcodeIds)
            ->get();

        $counts = \App\Models\ProductBarcode::where('shop_id', $shop?->id)
            ->where('is_sold', 0)
            ->where('is_printed', 1)
            ->when(!empty($exclude), function ($query) use ($exclude) {
                $query->whereNotIn('barcode_number', $exclude);
            })
            ->whereIn('product_id', $barcodes->pluck('product_id'))
            ->groupBy('product_id')
            ->selectRaw('product_id, COUNT(*) as count')
            ->pluck('count', 'product_id');

        $results = $barcodes->map(function ($bc) use ($counts) {
            $stock = $counts[$bc->product_id] ?? 0;
            return [
                'barcode' => $bc->barcode_number,
                'name' => $bc->product?->name ?? '',
                'design_no' => $bc->inwardProduct?->designMaster?->design_number ?? '',
                'rate' => (float)($bc->mrp ?? $bc->inwardProduct?->price ?? 0),
                'stock' => $stock
            ];
        });

        return response()->json($results);
    }

    public function searchCustomer($phone)
    {
        $customer = Customer::whereHas('user', function ($query) use ($phone) {
            $query->where('phone', 'like', "%{$phone}%");
        })->with('user')->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->user?->name ?? '',
            'phone' => $customer->user?->phone ?? '',
            'email' => $customer->user?->email ?? '',
            'address' => $customer->user?->address ?? ''
        ]);
    }

    public function checkoutGrid(POSCheckoutRequest $request, \App\Services\POS\PosOrderFinalizationService $finalizationService)
    {
        $shop = generaleSetting('shop');
        $date = now();
        
        $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', 1)
            ->first();

        if (!$financialYear) {
            return response()->json(['error' => __('Active financial year not found.')], 422);
        }

        try {
            $order = $finalizationService->finalize(
                $request->validated(),
                $shop,
                $financialYear,
                $request->input('idempotency_key'),
                $request->input('payment_attempt_id')
            );

            return response()->json([
                'success' => true,
                'message' => __('Order checked out successfully'),
                'invoice_url' => url('/shop/pos/' . $order->id . '/invoice'),
                'order' => new POSOrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Suspend / Hold a billing session.
     */
    public function holdOrder(Request $request)
    {
        $request->validate([
            'bill_label' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'customer_phone' => 'nullable|string|max:30',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'nullable|string|max:150',
            'subtotal' => 'required|numeric',
            'counter_id' => 'nullable|integer',
        ]);

        $shop = generaleSetting('shop');
        $cashier = auth()->user();

        $hold = \App\Repositories\POSHoldRepository::store([
            'shop_id' => $shop->id,
            'counter_id' => $request->counter_id,
            'cashier_id' => $cashier?->id,
            'bill_label' => $request->bill_label ?: ('Suspended ' . now()->format('h:i A')),
            'items' => $request->items,
            'customer_phone' => $request->customer_phone,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'subtotal' => $request->subtotal,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Bill suspended on hold successfully'),
            'hold' => $hold
        ]);
    }

    /**
     * Get all holds for the current shop.
     */
    public function getHolds()
    {
        $shop = generaleSetting('shop');
        $holds = \App\Repositories\POSHoldRepository::query()
            ->where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($holds);
    }

    /**
     * Retrieve data for a hold and delete it so cashier can resume billing.
     */
    public function recallHold($id)
    {
        $shop = generaleSetting('shop');
        $hold = \App\Repositories\POSHoldRepository::query()
            ->where('shop_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$hold) {
            return response()->json(['error' => __('Suspended bill not found')], 404);
        }

        $data = $hold->toArray();
        $hold->delete();

        return response()->json([
            'success' => true,
            'hold' => $data
        ]);
    }

    /**
     * Look up purchase details of an invoice to offer returns/refunds.
     */
    public function lookupReturnInvoice($invoice, \App\Services\POS\POSReturnService $returnService)
    {
        $shop = generaleSetting('shop');
        try {
            $order = $returnService->findOrderForReturn($shop, $invoice);

            // Calculate quantities returned in past sessions to show correct maximum returnable quantities
            $productsData = $order->products->map(function ($prod) use ($order) {
                $barcode = $prod->pivot->barcode_number;
                
                $alreadyReturned = \App\Models\POSReturnProduct::whereHas('returnHeader', function ($q) use ($order) {
                        $q->where('original_order_id', $order->id);
                    })
                    ->where('barcode_number', $barcode)
                    ->sum('qty');

                return [
                    'product_id' => $prod->id,
                    'name' => $prod->name,
                    'barcode' => $barcode,
                    'purchased_qty' => (int)$prod->pivot->quantity,
                    'returned_qty' => (int)$alreadyReturned,
                    'price' => (float)$prod->pivot->price,
                    'mrp' => (float)$prod->pivot->mrp,
                    'discount_amount' => (float)$prod->pivot->discount_amount,
                    'tax_percentage' => (float)$prod->pivot->tax_percentage,
                ];
            });

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'prefix' => $order->prefix,
                'payable_amount' => $order->payable_amount,
                'customer_name' => $order->customer?->user?->name ?? 'Walk-in Customer',
                'customer_phone' => $order->customer?->user?->phone ?? '9999999999',
                'products' => $productsData
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Submit return processing transaction.
     */
    public function processReturn(Request $request, \App\Services\POS\POSReturnService $returnService)
    {
        $request->validate([
            'order_id' => 'nullable|integer',
            'refund_method' => 'required|string|in:cash,online',
            'counter_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.barcode' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.reason' => 'nullable|string|max:200',
            'items.*.order_id' => 'nullable|integer',
        ]);

        $shop = generaleSetting('shop');
        $date = now();
        
        $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', 1)
            ->first();

        if (!$financialYear) {
            return response()->json(['error' => __('Active financial year not found.')], 422);
        }

        try {
            $itemsByOrder = [];
            foreach ($request->items as $item) {
                $orderId = $item['order_id'] ?? $request->order_id;
                if (!$orderId) {
                    throw new \Exception(__('Order ID missing for returned item.'));
                }
                $itemsByOrder[$orderId][] = $item;
            }

            $returnNos = [];
            $lastReturnNo = null;

            foreach ($itemsByOrder as $orderId => $orderItems) {
                $payload = [
                    'order_id' => $orderId,
                    'refund_method' => $request->refund_method,
                    'counter_id' => $request->counter_id,
                    'items' => $orderItems
                ];

                $returnVal = $returnService->processReturn(
                    $payload,
                    $shop,
                    $financialYear
                );
                if ($returnVal->return_no) {
                    $returnNos[] = $returnVal->return_no;
                    $lastReturnNo = $returnVal->return_no;
                }
            }

            $returnMsg = implode(', ', $returnNos);

            return response()->json([
                'success' => true,
                'message' => __('Return / Credit Note ' . $returnMsg . ' processed successfully.'),
                'return_no' => $lastReturnNo,
                'all_return_nos' => $returnNos,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Check if the cashier has an active open shift on the selected counter.
     */
    public function checkShiftStatus(Request $request, \App\Services\POS\POSShiftService $shiftService)
    {
        $request->validate([
            'counter_id' => 'required|integer',
        ]);

        $shop = generaleSetting('shop');
        $cashier = auth()->user();

        $shift = $shiftService->getActiveShift($shop->id, $request->counter_id, $cashier->id);

        return response()->json([
            'has_active_shift' => (bool)$shift,
            'shift' => $shift
        ]);
    }

    /**
     * Open a new register shift session.
     */
    public function openShift(Request $request, \App\Services\POS\POSShiftService $shiftService)
    {
        $request->validate([
            'counter_id' => 'required|integer',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $shop = generaleSetting('shop');
        $cashier = auth()->user();

        try {
            $shift = $shiftService->openShift(
                $shop->id,
                $request->counter_id,
                $cashier->id,
                $request->opening_cash
            );

            return response()->json([
                'success' => true,
                'message' => __('Shift session opened successfully.'),
                'shift' => $shift
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Fetch statistics for the current active shift.
     */
    public function getShiftStats(Request $request, \App\Services\POS\POSShiftService $shiftService)
    {
        $request->validate([
            'counter_id' => 'required|integer',
        ]);

        $shop = generaleSetting('shop');
        $cashier = auth()->user();

        $shift = $shiftService->getActiveShift($shop->id, $request->counter_id, $cashier->id);
        if (!$shift) {
            return response()->json(['error' => __('No active shift found on this counter.')], 404);
        }

        $stats = $shiftService->getShiftTransactions($shift);

        return response()->json([
            'success' => true,
            'shift' => $shift,
            'stats' => $stats
        ]);
    }

    /**
     * Close the register shift session.
     */
    public function closeShift(Request $request, \App\Services\POS\POSShiftService $shiftService)
    {
        $request->validate([
            'counter_id' => 'required|integer',
            'closing_cash' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:250',
        ]);

        $shop = generaleSetting('shop');
        $cashier = auth()->user();

        $shift = $shiftService->getActiveShift($shop->id, $request->counter_id, $cashier->id);
        if (!$shift) {
            return response()->json(['error' => __('No active shift found on this counter.')], 404);
        }

        try {
            $closedShift = $shiftService->closeShift(
                $shift,
                $request->closing_cash,
                $request->note
            );

            return response()->json([
                'success' => true,
                'message' => __('Shift session closed successfully.'),
                'shift' => $closedShift
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Save/Suspend Hold Bill.
     */
    public function saveHoldBill(Request $request, \App\Services\POS\HoldBillService $holdService)
    {
        $request->validate([
            'counter_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'payable_amount' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'salesman_id' => 'nullable|integer',
            'remarks' => 'nullable|string|max:500',
            'tax_details' => 'nullable|array',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|string|max:150',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.barcode' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.disc_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.disc_amt' => 'nullable|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0',
            'items.*.tax_amt' => 'nullable|numeric|min:0',
            'items.*.tax_name' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.size' => 'nullable|string',
            'items.*.salesman_id' => 'nullable|integer',
        ]);

        $shop = generaleSetting('shop');
        $date = now();
        $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', 1)
            ->first();

        if (!$financialYear) {
            return response()->json(['error' => __('Active financial year not found.')], 422);
        }

        try {
            $hold = $holdService->saveHold($request->all(), $shop, $financialYear);

            return response()->json([
                'success' => true,
                'message' => __('Bill suspended on hold successfully'),
                'hold_no' => $hold->hold_no,
                'hold' => $hold
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * List active hold bills. Filterable.
     */
    public function listHoldBills(Request $request)
    {
        $shop = generaleSetting('shop');
        $query = \App\Models\HoldBill::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->with(['cashier', 'customer.user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hold_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $holds = $query->get()->map(function ($h) {
            return [
                'id' => $h->id,
                'hold_no' => $h->hold_no,
                'customer_name' => $h->customer?->user?->name ?? $h->customer_name ?? 'Walk-in Customer',
                'customer_phone' => $h->customer?->user?->phone ?? $h->customer_phone ?? '-',
                'cashier_name' => $h->cashier?->name ?? 'Cashier',
                'amount' => (float)$h->payable_amount,
                'date' => $h->created_at->format('Y-m-d h:i A'),
                'status' => $h->status,
            ];
        });

        return response()->json($holds);
    }

    /**
     * Soft delete a hold bill.
     */
    public function deleteHoldBill($id)
    {
        $shop = generaleSetting('shop');
        $hold = \App\Models\HoldBill::where('shop_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$hold) {
            return response()->json(['error' => __('Hold bill not found')], 404);
        }

        // Soft delete
        $hold->delete();

        return response()->json([
            'success' => true,
            'message' => __('Hold bill removed successfully')
        ]);
    }

    /**
     * Restore suspended hold bill back to active POS state.
     */
    public function restoreHoldBill($id, \App\Services\POS\HoldBillService $holdService)
    {
        $shop = generaleSetting('shop');
        try {
            $hold = $holdService->restoreHold($id, $shop);

            return response()->json([
                'success' => true,
                'message' => __('Hold bill restored successfully'),
                'hold' => $hold
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Duplicate hold bill.
     */
    public function duplicateHoldBill($id, \App\Services\POS\HoldBillService $holdService)
    {
        $shop = generaleSetting('shop');
        $date = now();
        $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', 1)
            ->first();

        if (!$financialYear) {
            return response()->json(['error' => __('Active financial year not found.')], 422);
        }

        try {
            $hold = $holdService->duplicateHold($id, $shop, $financialYear);

            return response()->json([
                'success' => true,
                'message' => __('Hold bill duplicated successfully.'),
                'hold' => $hold
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Render temporary Hold receipt for printing.
     */
    public function printHoldReceipt($id)
    {
        $shop = generaleSetting('shop');
        $hold = \App\Models\HoldBill::where('shop_id', $shop->id)
            ->where('id', $id)
            ->with(['items.product', 'cashier'])
            ->firstOrFail();

        // Render simple thermal receipt page style
        return view('shop.pos.hold_receipt', compact('hold', 'shop'));
    }

    /**
     * Fetch list of previous billing history invoices (server-side filters + pagination).
     */
    public function salesHistoryIndex(Request $request, \App\Services\POS\POSHistoryService $historyService)
    {
        $shop = generaleSetting('shop');
        
        if ($request->ajax()) {
            if ($request->get('tab') === 'returns') {
                $date = now();
                $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('is_active', 1)
                    ->first();
                
                $counterId = $request->get('counter_id');
                $matchingCounterIds = \App\Services\POS\POSHistoryService::resolveMatchingCounterIds($counterId);

                // 1. Today
                $todayStart = now()->startOfDay();
                $todayEnd = now()->endOfDay();
                $todayReturns = \App\Models\POSReturn::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$todayStart, $todayEnd]);
                if (!empty($matchingCounterIds)) {
                    $todayReturns->whereIn('counter_id', $matchingCounterIds);
                }
                $statTodayAmt = (float)$todayReturns->sum('total_amount');
                $statTodayCount = (int)$todayReturns->count();

                // 2. This Week
                $weekStart = now()->startOfWeek();
                $weekEnd = now()->endOfWeek();
                $weekReturns = \App\Models\POSReturn::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$weekStart, $weekEnd]);
                if (!empty($matchingCounterIds)) {
                    $weekReturns->whereIn('counter_id', $matchingCounterIds);
                }
                $statWeekAmt = (float)$weekReturns->sum('total_amount');
                $statWeekCount = (int)$weekReturns->count();

                // 3. This Month
                $monthStart = now()->startOfMonth();
                $monthEnd = now()->endOfMonth();
                $monthReturns = \App\Models\POSReturn::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd]);
                if (!empty($matchingCounterIds)) {
                    $monthReturns->whereIn('counter_id', $matchingCounterIds);
                }
                $statMonthAmt = (float)$monthReturns->sum('total_amount');
                $statMonthCount = (int)$monthReturns->count();

                // 4. Financial Year
                $yearReturns = \App\Models\POSReturn::where('shop_id', $shop->id);
                if ($financialYear) {
                    $yearReturns->whereBetween('created_at', [$financialYear->start_date . ' 00:00:00', $financialYear->end_date . ' 23:59:59']);
                }
                if (!empty($matchingCounterIds)) {
                    $yearReturns->whereIn('counter_id', $matchingCounterIds);
                }
                $statYearAmt = (float)$yearReturns->sum('total_amount');
                $statYearCount = (int)$yearReturns->count();

                // Fetch returned products
                $query = \App\Models\POSReturnProduct::whereHas('returnHeader', function($q) use ($shop, $matchingCounterIds) {
                    $q->where('shop_id', $shop->id);
                    if (!empty($matchingCounterIds)) {
                        $q->whereIn('counter_id', $matchingCounterIds);
                    }
                })->with(['returnHeader.originalOrder', 'product', 'returnHeader.customer.user', 'returnHeader.cashier']);

                if ($request->filled('invoice_no')) {
                    $search = $request->get('invoice_no');
                    $query->where(function($q) use ($search) {
                        $q->where('barcode_number', 'like', "%{$search}%")
                          ->orWhere('reason', 'like', "%{$search}%")
                          ->orWhereHas('product', function($pq) use ($search) {
                              $pq->where('name', 'like', "%{$search}%");
                          })
                          ->orWhereHas('returnHeader', function($rhq) use ($search) {
                              $rhq->where('return_no', 'like', "%{$search}%")
                                  ->orWhereHas('originalOrder', function($ooq) use ($search) {
                                      $ooq->where('order_code', 'like', "%{$search}%");
                                  });
                          });
                    });
                }

                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $query->whereHas('returnHeader', function($q) use ($request) {
                        $q->whereBetween('created_at', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
                    });
                }

                $returns = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 10));

                $mappedItems = collect($returns->items())->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'return_no' => $item->returnHeader->return_no,
                        'return_id' => $item->returnHeader->id,
                        'original_invoice' => ($item->returnHeader->originalOrder->prefix ?? '') . '-' . ($item->returnHeader->originalOrder->order_code ?? ''),
                        'date' => $item->created_at->format('Y-m-d h:i A'),
                        'product_name' => $item->product->name ?? 'Product',
                        'barcode' => $item->barcode_number,
                        'qty' => $item->qty,
                        'rate' => (float)$item->rate,
                        'total' => (float)($item->rate * $item->qty),
                        'reason' => $item->reason ?? '-',
                        'customer' => $item->returnHeader->customer?->user?->name ?? 'Walk-in Customer',
                        'cashier' => $item->returnHeader->cashier->name ?? 'Cashier',
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $mappedItems,
                    'stats' => [
                        'today_amt' => $statTodayAmt,
                        'today_count' => $statTodayCount,
                        'week_amt' => $statWeekAmt,
                        'week_count' => $statWeekCount,
                        'month_amt' => $statMonthAmt,
                        'month_count' => $statMonthCount,
                        'year_amt' => $statYearAmt,
                        'year_count' => $statYearCount,
                    ],
                    'pagination' => [
                        'total' => $returns->total(),
                        'per_page' => $returns->perPage(),
                        'current_page' => $returns->currentPage(),
                        'last_page' => $returns->lastPage(),
                    ]
                ]);
            }

            $filters = $request->only([
                'invoice_no', 'customer', 'barcode', 'product_name',
                'salesman_id', 'payment_mode', 'status', 'date_from', 'date_to', 'amount', 'counter_id'
            ]);

            $orders = $historyService->getHistory($filters, $shop->id, $request->get('limit', 10));

            $items = collect($orders->items())->map(function ($order) {
                $status = strtolower($order->order_status->value ?? $order->order_status);
                $hasReturns = $order->posReturns->isNotEmpty();

                if ($status === 'delivered' && $hasReturns) {
                    $totalReturned = \App\Models\POSReturnProduct::whereIn('return_id', $order->posReturns->pluck('id'))->sum('qty');
                    $totalPurchased = $order->products->sum('pivot.quantity');

                    if ($totalReturned >= $totalPurchased) {
                        $status = 'Returned';
                    } else if ($totalReturned > 0) {
                        $status = 'Partial Returned';
                    }
                }

                return [
                    'id' => $order->id,
                    'invoice_no' => $order->prefix . '-' . $order->order_code,
                    'customer_name' => $order->customer?->user?->name ?? 'Walk-in Customer',
                    'customer_phone' => $order->customer?->user?->phone ?? '-',
                    'date' => $order->created_at->format('Y-m-d h:i A'),
                    'items_count' => $order->products->sum('pivot.quantity'),
                    'gross_amount' => (float)$order->total_amount,
                    'discount' => (float)$order->coupon_discount,
                    'tax_amount' => (float)$order->tax_amount,
                    'net_amount' => (float)$order->payable_amount,
                    'paid_amount' => (float)$order->payments->sum('amount'),
                    'payment_method' => str_replace(' Payment', '', $order->payment_method->value ?? $order->payment_method),
                    'cashier' => $order->cashier?->name ?? 'Cashier',
                    'salesman' => $order->salesman?->name ?? '-',
                    'billing_speed' => $order->billing_speed_formatted,
                    'billing_duration_seconds' => (int)$order->billing_duration_seconds,
                    'status' => $status,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ]
            ]);
        }

        // Return core dashboard UI
        return view('shop.pos.history', compact('shop'));
    }

    /**
     * Fetch key summary stats for history dashboard.
     */
    public function salesHistoryStats(\Illuminate\Http\Request $request, \App\Services\POS\POSHistoryService $historyService)
    {
        $shop = generaleSetting('shop');
        try {
            $counterId = $request->get('counter_id');
            $stats = $historyService->getStats($shop->id, $counterId);
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Void (Cancel) order and revert accounting ledger journal and inventory stock.
     */
    public function salesHistoryVoid($id, \App\Services\POS\POSHistoryService $historyService)
    {
        $shop = generaleSetting('shop');
        try {
            $order = $historyService->voidOrder($id, $shop->id);
            return response()->json([
                'success' => true,
                'message' => __('Order bill voided and cancelled successfully. Inventory & Ledger entries reversed.')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Duplicate bill - Load details to restore to active checkout POS grid.
     */
    public function salesHistoryDuplicate($id)
    {
        $shop = generaleSetting('shop');
        $order = Order::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('id', $id)
            ->with(['products' => function($q) {
                $q->withoutGlobalScopes();
            }, 'customer.user'])
            ->first();

        if (!$order) {
            return response()->json(['error' => __('Order not found.')], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    /**
     * Export sales history search filters to CSV.
     */
    public function salesHistoryExport(Request $request, \App\Services\POS\POSHistoryService $historyService)
    {
        $shop = generaleSetting('shop');
        $filters = $request->only([
            'invoice_no', 'customer', 'barcode', 'product_name',
            'salesman_id', 'payment_mode', 'status', 'date_from', 'date_to', 'amount', 'counter_id'
        ]);

        // Fetch up to 10000 limit for export reports
        $orders = $historyService->getHistory($filters, $shop->id, 10000);

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=POS_Sales_History_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Invoice No', 'Customer Name', 'Phone', 'Date',
            'Items Count', 'Gross Amount', 'Discount', 'GST / Tax', 'Net Amount', 'Paid Amount',
            'Payment Mode', 'Status'
        ];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->prefix . '-' . $order->order_code,
                    $order->customer?->user?->name ?? 'Walk-in Customer',
                    $order->customer?->user?->phone ?? '-',
                    $order->created_at->format('Y-m-d h:i A'),
                    $order->products->sum('pivot.quantity'),
                    $order->total_amount,
                    $order->coupon_discount,
                    $order->tax_amount,
                    $order->payable_amount,
                    $order->payments->sum('amount'),
                    str_replace(' Payment', '', $order->payment_method->value ?? $order->payment_method),
                    $order->order_status->value ?? $order->order_status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printReturnReceipt($idOrNo)
    {
        $returnHeader = \App\Models\POSReturn::with(['products.product', 'originalOrder', 'customer.user', 'cashier'])
            ->where('id', $idOrNo)
            ->orWhere('return_no', $idOrNo)
            ->orWhereHas('originalOrder', function ($q) use ($idOrNo) {
                if (str_contains($idOrNo, '-')) {
                    $lastHyphenPos = strrpos($idOrNo, '-');
                    $prefix = substr($idOrNo, 0, $lastHyphenPos);
                    $code = substr($idOrNo, $lastHyphenPos + 1);
                    $q->where('prefix', $prefix)->where('order_code', $code);
                } else {
                    $q->where('order_code', $idOrNo);
                }
            })
            ->latest()
            ->firstOrFail();

        $setting = generaleSetting('setting');
        $printerType = $setting?->pos_printer_type ?? 'thermal';

        if ($printerType === 'normal' || $printerType === 'a4') {
            return view('shop.pos.a4_return_print_layout', compact('returnHeader'));
        }

        return view('shop.pos.return_print_layout', compact('returnHeader'));
    }

    public function syncStatus()
    {
        $branchContext = app(\App\Services\BranchContext::class);
        $branch = $branchContext->getCurrentBranch();

        $lastSyncedAt = $branch ? $branch->last_synced_at?->toIso8601String() : null;
        $pendingCount = \App\Models\SyncQueue::where('status', 'pending')->count();
        $failedCount = \App\Models\SyncQueue::where('status', 'failed')->count();

        return response()->json([
            'last_synced_at' => $lastSyncedAt,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
        ]);
    }
}
