<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShiprocketService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.shiprocket.base_url'),
            '/'
        );
    }

    public function getToken(): string
    {
        return Cache::remember(
            'shiprocket_api_token',
            now()->addDays(9),
            function () {
                try {
                    $response = Http::acceptJson()
                        ->timeout(15)
                        ->post($this->baseUrl . '/auth/login', [
                            'email' => config('services.shiprocket.email'),
                            'password' => config('services.shiprocket.password'),
                        ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Shiprocket Auth Network Error: ' . $e->getMessage());
                    throw new RuntimeException('Shipping charges are temporarily unavailable. Please try again.', 0, $e);
                }

                if ($response->failed()) {
                    throw new RuntimeException(
                        $response->json('message')
                        ?? 'Shiprocket authentication failed'
                    );
                }

                $token = $response->json('token');

                if (! $token) {
                    throw new RuntimeException(
                        'Shiprocket token not found'
                    );
                }

                return $token;
            }
        );
    }

    public function serviceability(array $data): array
    {
        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->get($this->baseUrl . '/courier/serviceability/', [
                    'pickup_postcode' => $data['pickup_postcode'],
                    'delivery_postcode' => $data['delivery_postcode'],
                    'weight' => $data['weight'],
                    'cod' => $data['cod'] ? 1 : 0,
                    'declared_value' => $data['declared_value'] ?? 0,
                    'mode' => $data['mode'] ?? 'Surface',
                ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Serviceability Network Error: ' . $e->getMessage());
            throw new RuntimeException('Shipping charges are temporarily unavailable. Please try again.', 0, $e);
        }

        if ($response->unauthorized()) {
            Cache::forget('shiprocket_api_token');

            try {
                $response = Http::acceptJson()
                    ->withToken($this->getToken())
                    ->timeout(15)
                    ->get($this->baseUrl . '/courier/serviceability/', [
                        'pickup_postcode' => $data['pickup_postcode'],
                        'delivery_postcode' => $data['delivery_postcode'],
                        'weight' => $data['weight'],
                        'cod' => $data['cod'] ? 1 : 0,
                        'declared_value' => $data['declared_value'] ?? 0,
                        'mode' => $data['mode'] ?? 'Surface',
                    ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Shiprocket Serviceability Retry Network Error: ' . $e->getMessage());
                throw new RuntimeException('Shipping charges are temporarily unavailable. Please try again.', 0, $e);
            }
        }

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('message')
                ?? 'Unable to calculate shipping charge'
            );
        }

        return $response->json();
    }

    public function createAdhocOrder(array $payload): array
    {
        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(20)
                ->post($this->baseUrl . '/orders/create/adhoc', $payload);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Create Order Error: ' . $e->getMessage());
            throw new RuntimeException('Shiprocket service unreachable: ' . $e->getMessage(), 0, $e);
        }

        if ($response->unauthorized()) {
            Cache::forget('shiprocket_api_token');
            $response = Http::acceptJson()
                ->withToken($this->getToken())
                ->timeout(20)
                ->post($this->baseUrl . '/orders/create/adhoc', $payload);
        }

        if ($response->failed()) {
            $msg = $response->json('message') ?? $response->json('errors') ?? 'Failed to create order in Shiprocket';
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            throw new RuntimeException((string)$msg);
        }

        return $response->json() ?? [];
    }

    public function assignAwb(int|string $shipmentId, ?int $courierId = null): array
    {
        $payload = ['shipment_id' => $shipmentId];
        if ($courierId) {
            $payload['courier_id'] = $courierId;
        }

        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(20)
                ->post($this->baseUrl . '/courier/assign/awb', $payload);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Assign AWB Error: ' . $e->getMessage());
            throw new RuntimeException('AWB assignment failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->unauthorized()) {
            Cache::forget('shiprocket_api_token');
            $response = Http::acceptJson()
                ->withToken($this->getToken())
                ->timeout(20)
                ->post($this->baseUrl . '/courier/assign/awb', $payload);
        }

        if ($response->failed()) {
            $msg = $response->json('message') ?? 'Failed to assign AWB in Shiprocket';
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            throw new RuntimeException((string)$msg);
        }

        return $response->json() ?? [];
    }

    public function requestPickup(array|int|string $shipmentIds): array
    {
        $payload = [
            'shipment_id' => is_array($shipmentIds) ? $shipmentIds : [$shipmentIds],
        ];

        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(20)
                ->post($this->baseUrl . '/courier/generate/pickup', $payload);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Generate Pickup Error: ' . $e->getMessage());
            throw new RuntimeException('Pickup request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->unauthorized()) {
            Cache::forget('shiprocket_api_token');
            $response = Http::acceptJson()
                ->withToken($this->getToken())
                ->timeout(20)
                ->post($this->baseUrl . '/courier/generate/pickup', $payload);
        }

        if ($response->failed()) {
            $msg = $response->json('message') ?? 'Failed to request pickup in Shiprocket';
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            throw new RuntimeException((string)$msg);
        }

        return $response->json() ?? [];
    }

    public function trackByAwb(string $awbCode): array
    {
        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->get($this->baseUrl . '/courier/track/awb/' . $awbCode);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Tracking Error: ' . $e->getMessage());
            return [];
        }

        return $response->json() ?? [];
    }

    public function getPickupLocations(): array
    {
        try {
            $token = $this->getToken();
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->get($this->baseUrl . '/settings/company/pickup');
            return $response->json() ?? [];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Shiprocket Pickup Locations Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Prepare live shipping payload parameters from an Order instance.
     */
    public function buildOrderShippingData(\App\Models\Order $order): array
    {
        if (!$order->relationLoaded('products')) {
            $order->load('products');
        }
        if (!$order->relationLoaded('address')) {
            $order->load('address');
        }

        $setting = \App\Models\GeneraleSetting::first();
        $pickupPincode = $setting?->shiprocket_pickup_pincode
            ?? config('services.shiprocket.pickup_pincode')
            ?? '384170';

        $deliveryPincode = $order->address?->post_code;

        $totalWeight = 0.0;
        $totalVolumetric = 0.0;
        $maxLen = 10.0;
        $maxWid = 10.0;
        $maxHei = 10.0;

        foreach ($order->products as $product) {
            $qty = max((int) ($product->pivot?->quantity ?? 1), 1);
            $weight = (float) ($product->weight ?? 0);
            $length = (float) ($product->length ?? 10);
            $width = (float) ($product->width ?? 10);
            $height = (float) ($product->height ?? 10);

            $totalWeight += $weight * $qty;
            $volumetricItem = (($length * $width * max($height, 10)) / 5000.0) * $qty;
            $totalVolumetric += $volumetricItem;

            if ($length > $maxLen) $maxLen = $length;
            if ($width > $maxWid) $maxWid = $width;
            if ($height > $maxHei) $maxHei = $height;
        }

        $applicableWeight = max($totalWeight, $totalVolumetric);

        $paymentMethodStr = is_string($order->payment_method)
            ? $order->payment_method
            : ($order->payment_method?->value ?? (string) $order->payment_method);

        $isCod = strtolower((string)$paymentMethodStr) === 'cash' || strtolower((string)$paymentMethodStr) === 'cash payment';

        return [
            'pickup_postcode' => (string) $pickupPincode,
            'delivery_postcode' => (string) ($deliveryPincode ?? ''),
            'dead_weight' => round($totalWeight, 2),
            'volumetric_weight' => round($totalVolumetric, 2),
            'weight' => round($applicableWeight, 2),
            'length' => max($maxLen, 10),
            'breadth' => max($maxWid, 10),
            'height' => max($maxHei, 10),
            'cod' => $isCod,
            'declared_value' => (float) ($order->payable_amount ?? $order->total_amount ?? 0),
            'mode' => 'Surface',
            'order_value' => (float) ($order->payable_amount ?? $order->total_amount ?? 0),
        ];
    }

    /**
     * Fetch and format available courier partners for a given Order.
     */
    public function getServiceabilityForOrder(\App\Models\Order $order): array
    {
        $shippingData = $this->buildOrderShippingData($order);

        if (empty($shippingData['pickup_postcode'])) {
            throw new \Exception(__('Pickup Pincode is not configured in Shiprocket settings.'));
        }

        if (empty($shippingData['delivery_postcode'])) {
            throw new \Exception(__('Delivery postcode is missing from shipping address. Please update the shipping address.'));
        }

        if (($shippingData['weight'] ?? 0) <= 0) {
            throw new \Exception(__('Unable to calculate courier rates because shipment weight is missing. Please configure the product/package weight before shipping.'));
        }

        $res = $this->serviceability($shippingData);

        $courierList = data_get($res, 'data.available_courier_companies', []);
        $recommendedId = data_get($res, 'data.recommended_courier_company_id')
            ?? data_get($res, 'data.shiprocket_recommended_courier_id');

        $formattedCouriers = [];

        foreach ($courierList as $c) {
            $rate = (float) ($c['rate'] ?? 0);
            $whatsappCharges = (float) ($c['whatsapp_charges'] ?? 0);
            $totalCost = round($rate + $whatsappCharges, 2);

            $isRecommended = ($recommendedId && $c['courier_company_id'] == $recommendedId)
                || (!empty($c['recommended_lt']) && $c['recommended_lt'] == 1);

            $isSurface = !empty($c['is_surface']) || str_contains(strtolower($c['courier_name'] ?? ''), 'surface');
            $isAir = !empty($c['air_max_weight']) && (float)$c['air_max_weight'] > 0 && !str_contains(strtolower($c['courier_name'] ?? ''), 'surface');

            $formattedCouriers[] = [
                'courier_company_id' => $c['courier_company_id'],
                'courier_name' => $c['courier_name'] ?? 'Courier Partner',
                'rating' => (float) ($c['rating'] ?? 4.0),
                'is_recommended' => $isRecommended,
                'mode' => $isSurface ? 'Surface' : ($isAir ? 'Air' : 'Standard'),
                'is_surface' => $isSurface,
                'is_air' => $isAir,
                'charge_weight' => (float) ($c['charge_weight'] ?? $shippingData['weight']),
                'min_weight' => (float) ($c['min_weight'] ?? 0.5),
                'etd' => $c['etd'] ?? 'Within 4-5 days',
                'estimated_delivery_days' => $c['estimated_delivery_days'] ?? '',
                'expected_pickup' => 'Tomorrow',
                'freight_charge' => (float) ($c['freight_charge'] ?? 0),
                'cod_charges' => (float) ($c['cod_charges'] ?? 0),
                'whatsapp_charges' => $whatsappCharges,
                'rate' => $rate,
                'total_cost' => $totalCost,
                'rto_charges' => (float) ($c['rto_charges'] ?? 0),
                'call_before_delivery' => $c['call_before_delivery'] ?? 'Available',
                'realtime_tracking' => $c['realtime_tracking'] ?? 'Real Time',
            ];
        }

        // Sort recommended first, then lowest rate
        usort($formattedCouriers, function ($a, $b) {
            if ($a['is_recommended'] !== $b['is_recommended']) {
                return $a['is_recommended'] ? -1 : 1;
            }
            return $a['total_cost'] <=> $b['total_cost'];
        });

        return [
            'order_id' => $order->id,
            'order_code' => $order->prefix . $order->order_code,
            'shipment_id' => $order->shiprocket_shipment_id,
            'pickup_postcode' => $shippingData['pickup_postcode'],
            'delivery_postcode' => $shippingData['delivery_postcode'],
            'payment_mode' => $shippingData['cod'] ? 'COD' : 'Prepaid',
            'dead_weight' => $shippingData['dead_weight'],
            'volumetric_weight' => $shippingData['volumetric_weight'],
            'applicable_weight' => $shippingData['weight'],
            'order_value' => $shippingData['order_value'],
            'couriers' => $formattedCouriers,
        ];
    }

    /**
     * Assign selected courier and generate AWB for an Order.
     */
    public function assignAwbToOrder(\App\Models\Order $order, int $courierCompanyId): array
    {
        if (empty($order->shiprocket_shipment_id)) {
            throw new RuntimeException('No Shiprocket shipment found for this order. Please create shipment first.');
        }

        if (!empty($order->shiprocket_awb_code)) {
            return [
                'status' => true,
                'already_assigned' => true,
                'awb_code' => $order->shiprocket_awb_code,
                'courier_name' => $order->shiprocket_courier_name,
                'courier_company_id' => $order->shiprocket_courier_company_id,
                'message' => 'AWB is already assigned to this shipment.',
            ];
        }

        $awbResp = $this->assignAwb($order->shiprocket_shipment_id, $courierCompanyId);
        $awbData = $awbResp['response']['data'] ?? [];

        $awbCode = $awbData['awb_code'] ?? $awbResp['awb_code'] ?? null;
        $courierName = $awbData['courier_name'] ?? $awbResp['courier_name'] ?? null;
        $assignedCourierId = $awbData['courier_company_id'] ?? $courierCompanyId;

        if (!$awbCode) {
            $msg = $awbData['error'] ?? $awbResp['message'] ?? 'Failed to generate AWB code from Shiprocket.';
            throw new RuntimeException((string) $msg);
        }

        $order->update([
            'shiprocket_awb_code' => (string) $awbCode,
            'shiprocket_courier_name' => (string) ($courierName ?? 'Assigned Courier'),
            'shiprocket_courier_company_id' => (string) $assignedCourierId,
            'shiprocket_status' => 'Ready To Ship',
        ]);

        return [
            'status' => true,
            'already_assigned' => false,
            'awb_code' => $awbCode,
            'courier_name' => $courierName,
            'courier_company_id' => $assignedCourierId,
            'message' => 'Courier assigned and AWB generated successfully!',
        ];
    }

    /**
     * Schedule pickup for an Order.
     */
    public function requestPickupForOrder(\App\Models\Order $order): array
    {
        if (empty($order->shiprocket_shipment_id)) {
            throw new RuntimeException('No Shiprocket shipment found for this order.');
        }

        if (empty($order->shiprocket_awb_code)) {
            throw new RuntimeException('AWB code must be generated before scheduling pickup.');
        }

        if ($order->shiprocket_pickup_status === 'Scheduled') {
            return [
                'status' => true,
                'already_scheduled' => true,
                'pickup_token' => $order->shiprocket_pickup_token,
                'pickup_date' => $order->shiprocket_pickup_date,
                'message' => 'Pickup is already scheduled for this shipment.',
            ];
        }

        $pickupResp = $this->requestPickup($order->shiprocket_shipment_id);
        $pickupData = $pickupResp['response'] ?? [];

        $pickupToken = $pickupData['pickup_token_number'] ?? null;
        $pickupDate = $pickupData['pickup_scheduled_date'] ?? null;

        $order->update([
            'shiprocket_pickup_status' => 'Scheduled',
            'shiprocket_pickup_token' => $pickupToken,
            'shiprocket_pickup_date' => $pickupDate,
        ]);

        return [
            'status' => true,
            'already_scheduled' => false,
            'pickup_token' => $pickupToken,
            'pickup_date' => $pickupDate,
            'message' => 'Pickup scheduled successfully in Shiprocket!',
        ];
    }
}