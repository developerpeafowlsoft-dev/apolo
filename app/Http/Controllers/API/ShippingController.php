<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class ShippingController extends Controller
{
    public function calculate(
        Request $request,
        ShiprocketService $shiprocketService
    ) {
//        $request->validate([
//            'address_id' => 'required|exists:addresses,id',
//            'is_buy_now' => 'nullable|boolean',
//            'payment_method' => 'nullable|string',
//        ]);
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'is_buy_now' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'shop_ids' => 'nullable|array',
            'shop_ids.*' => 'integer',
        ]);

        $user = auth()->user();
        $customer = $user->customer;

        $address = Address::query()
            ->where('id', $request->address_id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $isBuyNow = $request->boolean('is_buy_now');

//        $carts = $customer->carts()
//            ->with([
//                'product.shop',
//            ])
//            ->where('is_buy_now', $isBuyNow)
//            ->get();
        $carts = $customer->carts()
            ->with(['product.shop'])
            ->where('is_buy_now', $isBuyNow)
            ->when(
                ! $isBuyNow && $request->filled('shop_ids'),
                function ($query) use ($request) {
                    $query->whereIn('shop_id', $request->shop_ids);
                }
            )
            ->get();

        if ($carts->isEmpty()) {
            return $this->json(
                'Cart is empty',
                [],
                422
            );
        }

        $shopGroups = $carts->groupBy('shop_id');

        $totalShippingCharge = 0;
        $shopShipping = [];

        try {
            foreach ($shopGroups as $shopId => $shopCarts) {
                $shippingData = $this->prepareShippingData(
                    $shopCarts,
                    $address,
                    $request->payment_method
                );

                $response = $shiprocketService->serviceability(
                    $shippingData
                );

                $couriers = collect(
                    data_get(
                        $response,
                        'data.available_courier_companies',
                        []
                    )
                );

                if ($couriers->isEmpty()) {
                    return $this->json(
                        'No courier service available for selected address',
                        [],
                        422
                    );
                }

                // Cheapest available courier
                $selectedCourier = $couriers
                    ->filter(fn ($courier) =>
                    isset($courier['rate'])
                    )
                    ->sortBy('rate')
                    ->first();

                if (! $selectedCourier) {
                    return $this->json(
                        'Shipping rate not available',
                        [],
                        422
                    );
                }

                $charge = (float) $selectedCourier['rate'];
                $totalShippingCharge += $charge;

                $shopShipping[] = [
                    'shop_id' => (int) $shopId,
                    'courier_company_id' =>
                        $selectedCourier['courier_company_id'] ?? null,
                    'courier_name' =>
                        $selectedCourier['courier_name'] ?? null,
                    'rate' => round($charge, 2),
                    'estimated_delivery_days' =>
                        $selectedCourier['estimated_delivery_days']
                        ?? null,
                    'etd' => $selectedCourier['etd'] ?? null,
                    'weight' => $shippingData['weight'],
                ];
            }

            return $this->json(
                'Shipping charge calculated successfully',
                [
                    'delivery_charge' =>
                        round($totalShippingCharge, 2),
                    'shop_shipping' => $shopShipping,
                ]
            );
        } catch (Throwable $e) {
            report($e);

            \Illuminate\Support\Facades\Log::error('Shipping calculation exception: ' . $e->getMessage(), [
                'exception' => $e,
                'address_id' => $request->address_id ?? null,
            ]);

            $message = $e->getMessage();

            if (
                empty($message) ||
                str_contains($message, 'cURL error') ||
                str_contains($message, 'Could not resolve host') ||
                str_contains($message, 'shiprocket') ||
                str_contains($message, 'Connection timed out') ||
                str_contains($message, 'Failed to connect') ||
                str_contains($message, 'SSL') ||
                str_contains($message, 'GuzzleHttp') ||
                str_contains($message, 'http://') ||
                str_contains($message, 'https://')
            ) {
                $message = 'Shipping charges are temporarily unavailable. Please try again.';
            }

            return $this->json(
                $message,
                [],
                422
            );
        }
    }

    private function prepareShippingData(
        Collection $carts,
        Address $address,
        ?string $paymentMethod
    ): array {
        $actualWeight = 0;
        $totalVolume = 0;
        $declaredValue = 0;

        foreach ($carts as $cart) {
            $product = $cart->product;
            $quantity = max((int) $cart->quantity, 1);

            $weight = (float) (
                $product->weight
                ?? 0
            );


            $length = (float) (
                $product->length
                ?? 0
            );

            $width = (float) (
                $product->width
                ?? 0
            );

            $height = (float) (
                $product->height
                ?? 0
            );

            if (
                $weight <= 0 ||
                $length <= 0 ||
                $width <= 0 ||
                $height <= 0
            ) {
                throw new \RuntimeException(
                    "Shipping dimensions missing for {$product->name}"
                );
            }

            $actualWeight += $weight * $quantity;
            $totalVolume +=
                ($length * $width * $height) * $quantity;

            $sellingPrice = (float) (
                $cart->mrp
                ?? $cart->price
                ?? $product->price
                ?? 0
            );

            $declaredValue += $sellingPrice * $quantity;
        }

        $volumetricWeight = $totalVolume / 5000;
        $chargeableWeight = max(
            $actualWeight,
            $volumetricWeight
        );

        $generaleSetting = generaleSetting('setting');

        $pickupPostcode = $generaleSetting?->shiprocket_pickup_pincode;

        if (! $pickupPostcode) {
            throw new \RuntimeException(
                'Shiprocket pickup pincode is missing in Business Settings'
            );
        }

        if (! $address->post_code) {
            throw new \RuntimeException(
                'Delivery pincode is missing'
            );
        }

        return [
            'pickup_postcode' => $pickupPostcode,
            'delivery_postcode' => $address->post_code,
            'weight' => round(
                max($chargeableWeight, 0.5),
                2
            ),
            'cod' => strtolower((string) $paymentMethod) === 'cash',
            'declared_value' => round($declaredValue, 2),
            'mode' => 'Surface',
        ];
    }
}