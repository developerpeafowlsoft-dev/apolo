<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Services\NotificationServices;
use App\Services\ShiprocketService;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class OrderController extends Controller
{
    /**
     * Display the order list with filter status.
     */
    public function index($status = null)
    {
        $status = $status ? str_replace('_', ' ', $status) : '';

        $shop = generaleSetting('shop');

        if (! $shop) {
            abort(404, __('Shop not found.'));
        }

        $orders = OrderRepository::query()
            ->where('shop_id', $shop->id)
            ->when($status, function ($query) use ($status) {
                $query->where('order_status', $status);
            })
            ->latest('id')
            ->paginate(20);

        $statusCounts = OrderRepository::query()
            ->where('shop_id', $shop->id)
            ->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status')
            ->toArray();

        $totalOrdersCount = array_sum($statusCounts);

        return view('shop.order.index', compact('orders', 'status', 'statusCounts', 'totalOrdersCount'));
    }

    /**
     * Display the order details.
     */
    public function show($orderId)
    {
        $shop = generaleSetting('shop');

        if (! $shop) {
            abort(404, __('Shop not found.'));
        }

        $order = OrderRepository::query()
            ->withoutGlobalScopes()
            ->where('id', $orderId)
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        $orderStatus = OrderStatus::cases();

        $riders = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();

        return view('shop.order.show', compact('order', 'orderStatus', 'riders'));
    }

    /**
     * Update the order status.
     */
    public function statusChange(Order $order, Request $request)
    {
        $shop = generaleSetting('shop');

        if (! $shop || $order->shop_id != $shop->id) {
            abort(404, __('Order not found.'));
        }

        $request->validate(['status' => 'required']);

        $order->update(['order_status' => $request->status]);

        if ($request->status == OrderStatus::DELIVERED->value) {
            $this->updateWalletAndTransaction($order);
            try {
                app(\App\Services\Accounting\OnlineOrderAccountingService::class)->postOnlineOrderSalesVoucher($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Accounting posting failed on shop order delivered: ' . $e->getMessage());
            }
        }

        if ($request->status == OrderStatus::CANCELLED->value) {
            try {
                app(\App\Services\Accounting\OnlineOrderAccountingService::class)->postOnlineOrderCancellationVoucher($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Accounting reversal failed on shop order cancelled: ' . $e->getMessage());
            }

            foreach ($order->products as $product) {

                $qty = $product->pivot->quantity;

                $product->update(['quantity' => $product->quantity + $qty]);

                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                    if ($flashSaleProduct && $product->pivot?->price) {
                        if ($flashSaleProduct->pivot->sale_quantity >= $qty && ($product->pivot?->price == $flashSaleProduct->pivot->price)) {
                            $flashSale->products()->updateExistingPivot($product->id, [
                                'sale_quantity' => $flashSaleProduct->pivot->sale_quantity - $qty,
                            ]);
                        }
                    }
                }
            }
        }

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status.' order code: '.$order->prefix.$order->order_code;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];
        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Order status updated successfully.'));
    }

    /**
     * Update the payment status.
     */
    public function paymentStatusToggle(Order $order)
    {
        $shop = generaleSetting('shop');

        if (! $shop || $order->shop_id != $shop->id) {
            abort(404, __('Order not found.'));
        }

        if ($order->payment_status->value == PaymentStatus::PAID->value) {
            return back()->with('error', __('When order is paid, payment status cannot be changed.'));
        }
        $order->update(['payment_status' => PaymentStatus::PAID->value]);

        if (!$order->pos_order) {
            try {
                app(\App\Services\Accounting\OnlineOrderAccountingService::class)->postOnlineOrderSalesVoucher($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Accounting posting failed on shop payment status toggle: ' . $e->getMessage());
            }
        }

        return back()->with('success', __('Payment status updated successfully'));
    }

    /**
     * Create Shiprocket shipment and request pickup.
     */
    public function createShipment(Order $order, ShiprocketService $shiprocketService)
    {
        $shop = generaleSetting('shop');

        if (! $shop || $order->shop_id != $shop->id) {
            abort(404, __('Order not found.'));
        }

        if ($order->shiprocket_shipment_id || $order->shiprocket_order_id) {
            return back()->with('info', __('Shipment is already created for this order.'));
        }

        try {
            $order->load(['address', 'customer.user', 'products.hsnMaster.subHsn', 'products.vatTax']);

            $address = $order->address;
            $customerName = $address?->name ?? $order->customer?->user?->name ?? 'Customer';
            $customerPhone = $address?->phone ?? $order->customer?->user?->phone ?? '9099999000';
            $customerEmail = $order->customer?->user?->email ?? 'customer@apolo.com';

            $orderItems = [];
            $totalWeight = 0;
            $maxLen = 10;
            $maxWid = 10;
            $maxHei = 10;

            foreach ($order->products as $product) {
                $qty = max((int) $product->pivot->quantity, 1);
                $sellingPrice = (float) ($product->pivot->price ?? $product->price ?? 0);
                $weight = (float) ($product->weight ?? 0.5);

                $hsnMaster = $product->hsnMaster ?? null;
                if (!$hsnMaster && !empty($product->hsn_master_id)) {
                    $hsnMaster = \App\Models\HsnMaster::with('subHsn')->find($product->hsn_master_id);
                }

                $hsnCode = (string) (
                    $product->hsn
                    ?? $product->hsn_code
                    ?? $hsnMaster?->hsn_code
                    ?? $product->pivot->hsn
                    ?? ''
                );

                $categoryName = (string) (
                    $product->categories?->first()?->name
                    ?? $product->category?->name
                    ?? ''
                );

                if ($hsnMaster) {
                    $taxRate = (float) $hsnMaster->getTaxForPrice($sellingPrice);
                } else {
                    $taxRate = (float) (
                        $product->pivot->tax_percentage
                        ?? $product->vatTax?->percentage
                        ?? $product->vat_tax_percentage
                        ?? 0
                    );
                }

                $itemDiscount = (float) (
                    $product->pivot->discount_amount
                    ?? 0
                );

                $unitPreDiscountPrice = $itemDiscount > 0
                    ? ($sellingPrice + ($itemDiscount / $qty))
                    : $sellingPrice;

                $orderItem = [
                    'name' => $product->name,
                    'sku' => $product->code ?? ('SKU-' . $product->id),
                    'units' => $qty,
                    'selling_price' => round($unitPreDiscountPrice, 2),
                    'discount' => round($itemDiscount / $qty, 2),
                    'tax' => round($taxRate, 2),
                    'hsn' => $hsnCode,
                ];

                if (! empty($categoryName)) {
                    $orderItem['category'] = $categoryName;
                    $orderItem['category_name'] = $categoryName;
                }

                $orderItems[] = $orderItem;

                $totalWeight += $weight * $qty;
                if (($product->length ?? 0) > $maxLen) $maxLen = (float) $product->length;
                if (($product->width ?? 0) > $maxWid) $maxWid = (float) $product->width;
                if (($product->height ?? 0) > $maxHei) $maxHei = (float) $product->height;
            }

            $paymentMethodStr = is_string($order->payment_method)
                ? $order->payment_method
                : ($order->payment_method?->value ?? (string) $order->payment_method);

            $paymentType = (strtolower($paymentMethodStr) === 'cash' || strtolower($paymentMethodStr) === 'cash payment')
                ? 'COD'
                : 'Prepaid';

            $pickupLocationsRes = $shiprocketService->getPickupLocations();
            $shippingAddresses = data_get($pickupLocationsRes, 'data.shipping_address', []);
            $primaryLocation = collect($shippingAddresses)->firstWhere('is_primary_location', 1)
                ?? collect($shippingAddresses)->first();

            $pickupLocation = $primaryLocation['pickup_location'] ?? 'Home';

            $addressLine1 = $address?->address_line ?? $address?->area ?? 'Main Road';
            $addressLine2 = $address?->address_line2 ?? $address?->flat_no ?? '';

            $shippingData = $shiprocketService->buildOrderShippingData($order);

            $payload = [
                'order_id' => $order->prefix . $order->order_code,
                'order_date' => $order->created_at->format('Y-m-d H:i'),
                'pickup_location' => $pickupLocation,
                'channel_id' => '',
                'comment' => 'Apolo E-commerce Order',
                'billing_customer_name' => $customerName,
                'billing_last_name' => '',
                'billing_address' => $addressLine1,
                'billing_address_2' => $addressLine2,
                'billing_city' => $address?->area ?? 'Visnagar',
                'billing_pincode' => $shippingData['delivery_postcode'],
                'billing_state' => $address?->state ?? 'Gujarat',
                'billing_country' => $address?->country?->name ?? 'India',
                'billing_email' => $customerEmail,
                'billing_phone' => $customerPhone,
                'shipping_is_billing' => true,
                'order_items' => $orderItems,
                'payment_method' => $paymentType,
                'shipping_charges' => (float) $order->delivery_charge,
                'giftwrap_charges' => 0,
                'transaction_charges' => 0,
                'total_discount' => (float) $order->coupon_discount,
                'sub_total' => (float) $order->total_amount,
                'length' => $shippingData['length'],
                'breadth' => $shippingData['breadth'],
                'height' => $shippingData['height'],
                'weight' => $shippingData['weight'],
            ];

            // 0. Prevent duplicate order creation if already exists
            if (!empty($order->shiprocket_shipment_id)) {
                return back()->with('info', __('Shipment is already created in Shiprocket (Shipment ID: ') . $order->shiprocket_shipment_id . ').');
            }

            \Illuminate\Support\Facades\Log::info('Shop Shiprocket Outbound Order Payload: ', $payload);

            $response = $shiprocketService->createAdhocOrder($payload);

            $shiprocketOrderId = $response['order_id'] ?? null;
            $shipmentId = $response['shipment_id'] ?? null;
            $status = $response['status'] ?? 'NEW';
            $awbCode = $response['awb_code'] ?? null;
            $courierName = $response['courier_name'] ?? null;
            $courierCompanyId = $response['courier_company_id'] ?? null;

            if (! $shipmentId && ! $shiprocketOrderId) {
                throw new \RuntimeException($response['message'] ?? 'Shiprocket did not return a valid shipment ID.');
            }

            $order->update([
                'shiprocket_order_id' => (string) $shiprocketOrderId,
                'shiprocket_shipment_id' => (string) $shipmentId,
                'shiprocket_status' => (string) $status,
                'shiprocket_awb_code' => (string) $awbCode,
                'shiprocket_courier_name' => (string) $courierName,
                'shiprocket_courier_company_id' => (string) $courierCompanyId,
                'shiprocket_pickup_status' => 'Pending Request',
            ]);

            return back()->with('success', __('Shiprocket order created successfully! Click "Ship Now" to select a courier partner.'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shop Shipment Creation Error: ' . $e->getMessage(), ['exception' => $e]);
            $msg = $e->getMessage();
            if (str_contains($msg, 'cURL') || str_contains($msg, 'Could not resolve host')) {
                $msg = 'Shiprocket API is temporarily unreachable. Please try again.';
            }
            return back()->with('error', $msg);
        }
    }

    /**
     * Fetch available couriers with live rates and ETA for a shop order.
     */
    public function getShiprocketCouriers(Order $order, ShiprocketService $shiprocketService)
    {
        $currentShopId = auth()->user()->shop?->id;
        if ($order->shop_id && $currentShopId && $order->shop_id != $currentShopId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized order access.'], 403);
        }

        try {
            $data = $shiprocketService->getServiceabilityForOrder($order);
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shop Shiprocket Couriers Fetch Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Assign selected courier partner and generate AWB code for shop order.
     */
    public function assignShiprocketCourier(Request $request, Order $order, ShiprocketService $shiprocketService)
    {
        $currentShopId = auth()->user()->shop?->id;
        if ($order->shop_id && $currentShopId && $order->shop_id != $currentShopId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized order access.'], 403);
        }

        $request->validate([
            'courier_company_id' => 'required|numeric',
        ]);

        try {
            $result = $shiprocketService->assignAwbToOrder($order, (int) $request->courier_company_id);
            return response()->json([
                'status' => true,
                'data' => $result,
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shop Shiprocket Assign Courier Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Request pickup scheduling for shop order.
     */
    public function scheduleShiprocketPickup(Request $request, Order $order, ShiprocketService $shiprocketService)
    {
        $currentShopId = auth()->user()->shop?->id;
        if ($order->shop_id && $currentShopId && $order->shop_id != $currentShopId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Unauthorized order access.'], 403);
            }
            return back()->with('error', 'Unauthorized order access.');
        }

        try {
            $result = $shiprocketService->requestPickupForOrder($order);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'data' => $result,
                    'message' => $result['message'],
                ]);
            }
            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shop Shiprocket Schedule Pickup Error: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Track live shipment status by AWB for shop order.
     */
    public function trackShiprocketShipment(Order $order, ShiprocketService $shiprocketService)
    {
        $currentShopId = auth()->user()->shop?->id;
        if ($order->shop_id && $currentShopId && $order->shop_id != $currentShopId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized order access.'], 403);
        }

        try {
            if (empty($order->shiprocket_awb_code)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No AWB code available for tracking.',
                ], 422);
            }

            $tracking = $shiprocketService->trackByAwb($order->shiprocket_awb_code);
            return response()->json([
                'status' => true,
                'data' => $tracking,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shop Shiprocket Track Shipment Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function downloadInvoice($id)
    {
        return $this->paymentSlip($id);
    }

    public function paymentSlip($id)
    {
        $order = Order::with(['products.hsnMaster.subHsn', 'customer.user', 'address'])->findOrFail($id);
        $generaleSetting = generaleSetting('setting');

        $invoiceUrl = route('shop.pos.invoice', $order->uuid ?? $order->id);
        $qrCodeImage = null;
        try {
            $qrCode = new \Endroid\QrCode\QrCode($invoiceUrl);
            $qrCode->setSize(80);
            $writer = new \Endroid\QrCode\Writer\PngWriter;
            $qrCodeImage = $writer->write($qrCode)->getDataUri();
        } catch (\Throwable $e) {
            // fallback if EndroidQrCode unavailable
        }

        // pdf config
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $fontData['kalpurush'] = [
            'R' => 'kalpurush.ttf',
        ];

        $paperSize = 'A4';

        $mPdf = new Mpdf([
            'mode' => 'UTF-8',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/public/mpdf_tmp'),
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData,
            'format' => $paperSize,
        ]);

        $view = view('PDF.payment-slip', compact('order', 'generaleSetting', 'qrCodeImage'))->render();
        $mPdf->WriteHTML($view);

        $pdfContent = $mPdf->Output('payment-slip-'.$order->prefix.$order->order_code.'.pdf', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="payment-slip-'.$order->prefix.$order->order_code.'.pdf"',
        ]);
    }

    private function updateWalletAndTransaction($order)
    {

        $generaleSetting = generaleSetting('setting');

        $commission = 0;

        if ($generaleSetting?->commission_charge != 'monthly') {

            if ($generaleSetting?->commission_type != 'fixed') {
                $commission = $order->total_amount * $generaleSetting->commission / 100;
            } else {
                $commission = $generaleSetting->commission ?? 0;
            }
        }

        $order->update([
            'delivery_date' => now(),
            'delivered_at' => now(),
            'payment_status' => PaymentStatus::PAID->value,
            'admin_commission' => $commission,
        ]);

        $wallet = $order->shop->user->wallet;

        WalletRepository::updateByRequest($wallet, $order->payable_amount, 'credit');

        TransactionRepository::storeByRequest($wallet, $commission, 'debit', true, true, 'admin commission', 'order');
    }
}
