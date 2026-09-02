<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice / Payment Slip</title>
    <style>
        body {
            font-family: sans-serif;
            color: #000;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 9px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .no-border-table td, .no-border-table th {
            border: none;
        }
        .header-table td {
            border: 1px solid #000;
            vertical-align: top;
        }
        .logo-img {
            max-height: 45px;
            max-width: 130px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .fz-12 { font-size: 12px; }
        .fz-10 { font-size: 10px; }
        .fz-9 { font-size: 9px; }
        .fz-8 { font-size: 8px; }
        .mt-10 { margin-top: 10px; }
        .mb-5 { margin-bottom: 5px; }
    </style>
</head>
<body>

@php
    $setting = $generaleSetting ?? generaleSetting('setting');
    $customerName = $order->customer?->user?->name ?? $order->address?->name ?? 'Customer';
    $customerPhone = $order->address?->phone ?? $order->customer?->user?->phone ?? '';
    $area = $order->address?->area ?? 'Unjha';
    $state = $order->address?->state ?? 'Gujarat';
    $orderCode = $order->prefix . $order->order_code;

    // Calculate HSN Breakdown
    $hsnSummary = [];

    foreach ($order->products as $product) {
        $qty = (float)($product->pivot->quantity ?? 1);
        $price = (float)($product->pivot->price ?? $product->price ?? 0);
        
        $hsnMaster = $product->hsnMaster ?? null;
        if (!$hsnMaster && !empty($product->hsn_master_id)) {
            $hsnMaster = \App\Models\HsnMaster::with('subHsn')->find($product->hsn_master_id);
        }
        $hsnCode = (string)($product->hsn ?? $product->hsn_code ?? $hsnMaster?->hsn_code ?? $product->pivot->hsn ?? '');

        if ($hsnMaster) {
            $taxRate = (float)$hsnMaster->getTaxForPrice($price);
        } else {
            $taxRate = (float)($product->pivot->tax_percentage ?? $product->vatTax?->percentage ?? $product->vat_tax_percentage ?? 0);
        }

        $gross = $price * $qty;
        $taxable = $taxRate > 0 ? ($gross * 100 / (100 + $taxRate)) : $gross;
        $taxAmt = $gross - $taxable;

        $key = ($hsnCode ?: '-') . '_' . $taxRate;
        if (!isset($hsnSummary[$key])) {
            $hsnSummary[$key] = [
                'hsn' => $hsnCode ?: '-',
                'rate' => $taxRate,
                'taxable' => 0,
                'tax' => 0,
            ];
        }
        $hsnSummary[$key]['taxable'] += $taxable;
        $hsnSummary[$key]['tax'] += $taxAmt;
    }
@endphp

<!-- Header Block -->
<table class="header-table">
    <tr>
        <td style="width: 65%; padding: 6px; vertical-align: top;">
            <div>
                <img src="{{ $setting?->logo ? public_path($setting->logo) : asset('assets/logo.png') }}" class="logo-img" alt="Apolo" />
            </div>
            <div class="fz-9" style="line-height: 1.4; margin-top: 12px;">
                {{ $setting?->address ?? 'Jay Vijay Society Rd, near harikrupa transport, Jaimin, Unjha, Gujarat 384170' }}
                <strong style="margin-left: 6px;">Mo-{{ $setting?->mobile ?? '8866583374' }}</strong>
            </div>
        </td>
        <td style="width: 35%; padding: 0;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td class="fz-9 font-bold" style="border-top: none; border-left: none; border-right: none;">GSTIN No. : {{ $setting?->gstin ?? '24ABYPP9859K1ZE' }}</td>
                </tr>
                <tr>
                    <td class="font-bold text-center fz-11" style="background-color: #f2f2f2; border-left: none; border-right: none;">TAX INVOICE</td>
                </tr>
                <tr>
                    <td class="fz-10" style="border-left: none; border-right: none;">
                        <strong>Bill No:</strong> #{{ $orderCode }}
                    </td>
                </tr>
                <tr>
                    <td class="fz-10" style="border-bottom: none; border-left: none; border-right: none;">
                        <strong>Date :</strong> {{ $order->created_at->format('d/m/Y') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Buyer Info Block -->
<table style="width: 100%; margin-top: -1px;">
    <tr>
        <td style="width: 65%;">
            <strong>Buyer :</strong> {{ strtoupper($customerName) }} {{ $customerPhone ? ', ' . $customerPhone : '' }}
        </td>
        <td style="width: 35%;">
            <strong>Place of Supply :</strong> {{ $area }}, {{ $state }}
        </td>
    </tr>
</table>

<!-- Main Items Table -->
<table class="mt-10" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 26%;">Item / Product</th>
            <th style="width: 6%;">Size</th>
            <th style="width: 8%;">Color</th>
            <th style="width: 10%;">HSN</th>
            <th style="width: 16%;">GST Rate</th>
            <th style="width: 6%;">Qty</th>
            <th style="width: 9%;">Rate</th>
            <th style="width: 9%;">Discount</th>
            <th style="width: 10%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->products as $index => $product)
            @php
                $qty = (float)($product->pivot->quantity ?? 1);
                $price = (float)($product->pivot->price ?? $product->price ?? 0);
                
                $hsnMaster = $product->hsnMaster ?? null;
                if (!$hsnMaster && !empty($product->hsn_master_id)) {
                    $hsnMaster = \App\Models\HsnMaster::with('subHsn')->find($product->hsn_master_id);
                }
                $hsnCode = (string)($product->hsn ?? $product->hsn_code ?? $hsnMaster?->hsn_code ?? $product->pivot->hsn ?? '-');

                if ($hsnMaster) {
                    $taxRate = (float)$hsnMaster->getTaxForPrice($price);
                } else {
                    $taxRate = (float)($product->pivot->tax_percentage ?? $product->vatTax?->percentage ?? $product->vat_tax_percentage ?? 0);
                }

                $grossAmount = $price * $qty;
                $taxableAmount = $taxRate > 0 ? ($grossAmount * 100 / (100 + $taxRate)) : $grossAmount;
                $itemTaxAmount = $grossAmount - $taxableAmount;
                $itemDiscount = (float)($product->pivot->discount_amount ?? 0);
                $size = $product->size ?? $product->pivot->size ?? '-';
                $color = $product->color ?? $product->pivot->color ?? '-';
            @endphp
            <tr>
                <td style="text-align: left;">{{ $product->name }}</td>
                <td class="text-center">{{ $size }}</td>
                <td class="text-center">{{ $color }}</td>
                <td class="text-center">{{ $hsnCode }}</td>
                <td class="text-center">{{ showCurrency(number_format(round($itemTaxAmount, 2), 2, '.', '')) }} ({{ number_format($taxRate, 0) }}%)</td>
                <td class="text-center">{{ number_format($qty, 2) }}</td>
                <td class="text-right">{{ number_format($price, 2) }}</td>
                <td class="text-right">{{ number_format($itemDiscount, 2) }}</td>
                <td class="text-right font-bold">{{ number_format($grossAmount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- HSN Summary Breakdown & Totals Table -->
<table class="mt-10" style="width: 100%; border: none;">
    <tr>
        <!-- Left: HSN Breakdown Table -->
        <td style="width: 58%; vertical-align: top; border: none; padding: 0;">
            <table style="width: 98%;">
                <thead>
                    <tr>
                        <th rowspan="2">HSN Code</th>
                        <th rowspan="2">GST Rate</th>
                        <th rowspan="2">Taxable Value</th>
                        <th colspan="2">SGST</th>
                        <th colspan="2">CGST</th>
                    </tr>
                    <tr>
                        <th>Tax %</th>
                        <th>Amount</th>
                        <th>Tax %</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hsnSummary as $row)
                        @php
                            $halfRate = $row['rate'] / 2;
                            $halfTax = $row['tax'] / 2;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $row['hsn'] }}</td>
                            <td class="text-center">{{ number_format($row['rate'], 2) }} %</td>
                            <td class="text-right">{{ number_format($row['taxable'], 2) }}</td>
                            <td class="text-center">{{ number_format($halfRate, 2) }}</td>
                            <td class="text-right">{{ number_format($halfTax, 2) }}</td>
                            <td class="text-center">{{ number_format($halfRate, 2) }}</td>
                            <td class="text-right">{{ number_format($halfTax, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </td>

        <!-- Right: Totals Summary Table -->
        <td style="width: 42%; vertical-align: top; border: none; padding: 0;">
            <table style="width: 100%;">
                <tr>
                    <td class="text-right font-bold" style="width: 50%;">Amount :</td>
                    <td class="text-right font-bold" style="width: 50%;">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @if(($order->delivery_charge ?? 0) > 0)
                <tr>
                    <td class="text-right font-bold">Delivery Charge :</td>
                    <td class="text-right font-bold">{{ number_format($order->delivery_charge, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-right font-bold" style="background-color: #f2f2f2;">Bill Total Amount :</td>
                    <td class="text-right font-bold fz-11" style="background-color: #f2f2f2;">{{ number_format($order->payable_amount, 2) }}</td>
                </tr>
                @php
                    $paymentStr = is_string($order->payment_method) ? $order->payment_method : ($order->payment_method->value ?? (string)$order->payment_method);
                    $isCod = str_contains(strtolower($paymentStr), 'cash') || str_contains(strtolower($paymentStr), 'cod');
                @endphp
                <tr>
                    <td class="text-right">By Cash :</td>
                    <td class="text-right">{{ $isCod ? number_format($order->payable_amount, 2) : '0.00' }}</td>
                </tr>
                <tr>
                    <td class="text-right">By Card / Online :</td>
                    <td class="text-right">{{ !$isCod ? number_format($order->payable_amount, 2) : '0.00' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Footer Terms & Condition Box -->
<table class="mt-10" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top; padding: 6px;">
            <div class="font-bold mb-5">Terms & Condition</div>
            <div class="fz-9">*Payment not refundable./Product will not exchanged after 1 day.</div>
            <div class="fz-9 font-bold">*SUNDAY FULL-DAY OPEN.</div>
        </td>
        <td style="width: 25%; text-align: center; vertical-align: middle; padding: 6px;">
            <div class="fz-9 font-bold">E. & O.E.</div>
            @php
                if (empty($qrCodeImage)) {
                    try {
                        $invoiceUrl = route('shop.pos.invoice', $order->uuid ?? $order->id);
                        $qrCode = new \Endroid\QrCode\QrCode($invoiceUrl);
                        $qrCode->setSize(80);
                        $writer = new \Endroid\QrCode\Writer\PngWriter;
                        $qrCodeImage = $writer->write($qrCode)->getDataUri();
                    } catch (\Throwable $e) {
                        $qrCodeImage = null;
                    }
                }
            @endphp
            @if(!empty($qrCodeImage))
                <div style="margin-top: 3px; margin-bottom: 3px; text-align: center;">
                    <img src="{{ $qrCodeImage }}" style="width: 40px; height: 40px; display: inline-block;" alt="QR" />
                </div>
            @endif
            <div class="fz-9">Scan to View Digital Bill</div>
        </td>
        <td style="width: 25%; text-align: center; vertical-align: middle; padding: 6px;">
            <div class="fz-9 font-bold">Follow Us On</div>
            <div class="fz-9 font-bold" style="color: #c13584; margin-top: 2px;">Instagram</div>
            <div class="fz-9 font-bold" style="margin-top: 2px;">APOLO_FAMILY_SHOWROOM_</div>
        </td>
    </tr>
</table>

<div class="font-bold fz-9 mt-10" style="text-align: left;">
    Item Rate Is Including Tax Amount
</div>

</body>
</html>
