<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - #{{ $order->prefix }}{{ $order->order_code }}</title>
    <style>
        body {
            font-family: sans-serif;
            color: #000;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        .invoice-page {
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            background-color: #fff;
            padding: 10mm;
            box-sizing: border-box;
            margin: 0 auto;
        }
        .invoice-page table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-page th, .invoice-page td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 9px;
        }
        .invoice-page th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .invoice-page .no-border-table td, .invoice-page .no-border-table th {
            border: none;
        }
        .invoice-page .header-table td {
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
        .fz-11 { font-size: 11px; }
        .fz-10 { font-size: 10px; }
        .fz-9 { font-size: 9px; }
        .fz-8 { font-size: 8px; }
        .mt-10 { margin-top: 10px; }
        .mb-5 { margin-bottom: 5px; }

        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }
            .invoice-page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

<div class="invoice-page">

@php
    $setting = $generaleSetting ?? generaleSetting('setting');
    $customerName = $order->customer?->user?->name ?? $order->address?->name ?? $order->customer_name ?? 'Walk-in';
    $customerPhone = $order->address?->phone ?? $order->customer?->user?->phone ?? $order->customer_phone ?? '';
    $area = $order->address?->area ?? $order->customer_area ?? 'Unjha';
    $state = $order->address?->state ?? $order->customer_state ?? 'Gujarat';
    $orderCode = $order->prefix . $order->order_code;

    if (empty($qrCodeImage)) {
        try {
            $isEInvoiceAvailable = !empty($order->e_invoice_irn) || ($setting?->is_e_invoice ?? false);
            if ($isEInvoiceAvailable) {
                $qrData = route('shop.pos.invoice', $order->uuid ?? $order->id);
            } else {
                $qrData = '#' . $orderCode;
            }

            $qrCode = new \Endroid\QrCode\QrCode($qrData);
            $qrCode->setSize(80);
            $writer = new \Endroid\QrCode\Writer\PngWriter;
            $qrCodeImage = $writer->write($qrCode)->getDataUri();
        } catch (\Throwable $e) {
            $qrCodeImage = null;
        }
    }

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

@php
    $exchangeInfo = null;
    if (!empty($order->instruction)) {
        $decodedIns = json_decode($order->instruction, true);
        if (is_array($decodedIns) && isset($decodedIns['type']) && $decodedIns['type'] === 'EXCHANGE') {
            $exchangeInfo = $decodedIns;
        }
    }
@endphp

<!-- Header Block -->
<table class="header-table">
    <tr>
        <td style="width: 65%; padding: 6px; vertical-align: top;">
            <div>
                <img src="{{ $setting?->logo ? asset($setting->logo) : asset('assets/logo.png') }}" class="logo-img" alt="Apolo" />
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
                    <td class="font-bold text-center fz-11" style="background-color: #f2f2f2; border-left: none; border-right: none;">{{ $exchangeInfo ? 'TAX & EXCHANGE INVOICE' : 'TAX INVOICE' }}</td>
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
    @if($exchangeInfo)
    <tr>
        <td colspan="2" style="background-color: #fffbeb; padding: 4px 6px; font-weight: bold; border: 1px dashed #f59e0b;">
            Exchange Ref Original Bill No: #{{ $exchangeInfo['original_invoice_no'] }} (Credit Note #{{ $exchangeInfo['return_no'] ?? '-' }})
        </td>
    </tr>
    @endif
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

        @if($exchangeInfo && !empty($exchangeInfo['returned_items']))
            <tr>
                <td colspan="9" class="font-bold text-left" style="background-color: #fff5f5; border: 1px solid #000; padding: 4px 5px; text-transform: uppercase;">
                    Returned Items (Original Bill Ref: #{{ $exchangeInfo['original_invoice_no'] }})
                </td>
            </tr>
            @foreach($exchangeInfo['returned_items'] as $retItem)
                @php
                    $retHsn = $retItem['hsn'] ?? null;
                    $retTaxRate = isset($retItem['tax_percentage']) ? (float)$retItem['tax_percentage'] : (isset($retItem['tax_rate']) ? (float)$retItem['tax_rate'] : null);
                    $barcodeStr = $retItem['barcode'] ?? null;

                    if ((empty($retHsn) || $retHsn === '-' || $retTaxRate === null) && !empty($barcodeStr)) {
                        $orderProd = \DB::table('order_products')
                            ->where('barcode_number', $barcodeStr)
                            ->latest('order_id')
                            ->first();

                        if ($orderProd) {
                            $prodModel = \App\Models\Product::with('hsnMaster')->find($orderProd->product_id);
                            if ($prodModel) {
                                $hsnMaster = $prodModel->hsnMaster;
                                if (!$hsnMaster && !empty($prodModel->hsn_master_id)) {
                                    $hsnMaster = \App\Models\HsnMaster::with('subHsn')->find($prodModel->hsn_master_id);
                                }
                                if (empty($retHsn) || $retHsn === '-') {
                                    $retHsn = (string)($prodModel->hsn ?? $prodModel->hsn_code ?? $hsnMaster?->hsn_code ?? $orderProd->hsn ?? '-');
                                }
                                if ($retTaxRate === null) {
                                    if ($hsnMaster) {
                                        $retTaxRate = (float)$hsnMaster->getTaxForPrice((float)$retItem['rate']);
                                    } else {
                                        $retTaxRate = (float)($orderProd->tax_percentage ?? $prodModel->vatTax?->percentage ?? 0);
                                    }
                                }
                            }
                        }
                    }

                    $retHsn = $retHsn ?: '-';
                    $retTaxRate = $retTaxRate ?? 0;
                    $retQty = (float)($retItem['qty'] ?? 1);
                    $retRate = (float)($retItem['rate'] ?? 0);
                    $retGross = $retRate * $retQty;
                    $retTaxable = $retTaxRate > 0 ? ($retGross * 100 / (100 + $retTaxRate)) : $retGross;
                    $retTaxAmt = $retGross - $retTaxable;
                @endphp
                <tr style="color: #991b1b;">
                    <td style="text-align: left;">[RETURNED] {{ $retItem['name'] }}</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-center">{{ $retHsn }}</td>
                    <td class="text-center">{{ showCurrency(number_format(round($retTaxAmt, 2), 2, '.', '')) }} ({{ number_format($retTaxRate, 0) }}%)</td>
                    <td class="text-center">{{ number_format($retQty, 2) }}</td>
                    <td class="text-right">{{ number_format($retRate, 2) }}</td>
                    <td class="text-right">0.00</td>
                    <td class="text-right font-bold">-{{ number_format($retGross, 2) }}</td>
                </tr>
            @endforeach
        @endif
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
                @if($exchangeInfo)
                <tr>
                    <td class="text-right font-bold">New Items Total :</td>
                    <td class="text-right font-bold">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right font-bold text-danger" style="color: #dc3545;">Returned Credit (Ref #{{ $exchangeInfo['original_invoice_no'] }}) :</td>
                    <td class="text-right font-bold text-danger" style="color: #dc3545;">-{{ number_format($exchangeInfo['returned_total'], 2) }}</td>
                </tr>
                @else
                <tr>
                    <td class="text-right font-bold" style="width: 50%;">Amount :</td>
                    <td class="text-right font-bold" style="width: 50%;">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @endif
                @if(($order->delivery_charge ?? 0) > 0)
                <tr>
                    <td class="text-right font-bold">Delivery Charge :</td>
                    <td class="text-right font-bold">{{ number_format($order->delivery_charge, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-right font-bold" style="background-color: #f2f2f2;">{{ $exchangeInfo ? 'Net Settlement Payable :' : 'Bill Total Amount :' }}</td>
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

</div>

</body>
</html>
