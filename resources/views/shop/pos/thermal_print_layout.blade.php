<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Thermal Invoice - #{{ $order->prefix }}{{ $order->order_code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, Consolas, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .receipt-container {
            width: 80mm;
            max-width: 100%;
            background-color: #fff;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            margin: 20px auto;
            border-radius: 6px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .header { margin-bottom: 8px; line-height: 1.3; }
        .header .company-name { font-size: 15px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; margin: 8px 0; text-transform: uppercase; }
        
        .metadata-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .metadata-table td { font-size: 11px; padding: 1px 0; vertical-align: top; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .item-table th, .item-table td { font-size: 11px; padding: 3px 0; vertical-align: top; }
        .item-table th { border-bottom: 1px dashed #000; }
        
        .summary-section { margin-top: 5px; line-height: 1.4; font-size: 11px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .summary-table td { padding: 1px 0; font-size: 11px; }
        
        .tax-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10px; }
        .tax-table th, .tax-table td { padding: 2px 0; }
        .tax-table th { border-bottom: 1px dashed #000; }
        
        .final-amount-box {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0;
            padding: 4px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        
        .e-invoice-section {
            font-size: 10px;
            margin: 8px 0;
            word-break: break-all;
            line-height: 1.3;
        }
        .e-invoice-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .qr-container {
            margin: 10px 0;
            display: flex;
            justify-content: center;
        }
        .qr-image {
            width: 120px;
            height: 120px;
        }
        
        .declaration {
            font-size: 9px;
            text-align: justify;
            line-height: 1.2;
            margin-top: 10px;
        }
        .footer {
            margin-top: 15px;
            font-size: 10px;
            line-height: 1.3;
        }
        
        /* Specific print rules to optimize thermal print */
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                background-color: #fff;
                display: block;
                min-height: auto;
            }
            .receipt-container {
                width: 100%;
                padding: 0;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">

    @php
        $settings = generaleSetting('setting');
        $companyName = $settings->name ?? 'Tallywebsolutions';
        $address = $settings->address ?? 'Ahmedabad, Gujarat 380001';
        $gstin = $settings->gstin ?? '27AAFCP0535R012';
        $cin = $settings->cin ?? 'U29120GJ2005PTC045398';
        $stateName = $settings->state_name ?? 'Gujarat, Code: 24';
        $email = $settings->email ?? 'tallywebsolutions@gmail.com';
        
        // Calculate items net total
        $itemsNetTotal = $order->products->sum(function($p) {
            return $p->pivot->price * $p->pivot->quantity;
        });
        
        // Calculate round-off
        $unroundedTotal = $itemsNetTotal + $order->tax_amount;
        $roundOff = $order->payable_amount - $unroundedTotal;
    @endphp

    <div class="header text-center">
        <span class="company-name">{{ $companyName }}</span><br>
        <span>{{ $address }}</span><br>
        @if($gstin)
            <span>GSTIN/UIN: {{ $gstin }}</span><br>
        @endif
        @if($stateName)
            <span>State Name: {{ $stateName }}</span><br>
        @endif
        @if($cin)
            <span>CIN: {{ $cin }}</span><br>
        @endif
        @if($email)
            <span>E-Mail: {{ $email }}</span>
        @endif
    </div>

    @php
        $exchangeInfo = null;
        if (!empty($order->instruction)) {
            $decodedIns = json_decode($order->instruction, true);
            if (is_array($decodedIns) && isset($decodedIns['type']) && $decodedIns['type'] === 'EXCHANGE') {
                $exchangeInfo = $decodedIns;
            }
        }
    @endphp

    <div class="divider"></div>
    <div class="title text-center">{{ $exchangeInfo ? 'TAX & EXCHANGE INVOICE' : 'Tax Invoice' }}</div>
    <div class="divider"></div>

    <table class="metadata-table">
        <tr>
            <td width="50%"><strong>Bill No:</strong> {{ $order->prefix }}-{{ $order->order_code }}</td>
            <td width="50%" class="text-right"><strong>Time:</strong> {{ $order->created_at->format('h:i A') }}</td>
        </tr>
        <tr>
            <td width="50%"><strong>Date:</strong> {{ $order->created_at->format('d-M-y') }}</td>
            <td width="50%" class="text-right"><strong>Cashier:</strong> {{ $order->cashier?->name ?? (auth()->user()?->first_name ?? 'POS') }}</td>
        </tr>
        <tr>
            <td width="50%"><strong>Salesperson:</strong> {{ $order->salesman?->name ?? 'Self' }}</td>
            <td width="50%" class="text-right"><strong>Bill Time:</strong> {{ $order->billing_speed_formatted }}</td>
        </tr>
        @if($exchangeInfo)
        <tr>
            <td colspan="2" style="background:#fffbeb; padding:2px 4px; border:1px dashed #f59e0b; margin-top:2px;">
                <strong>Exchange Ref:</strong> #{{ $exchangeInfo['original_invoice_no'] }} (CN #{{ $exchangeInfo['return_no'] ?? '-' }})
            </td>
        </tr>
        @endif
        <tr>
            <td colspan="2"><strong>Party Name:</strong> {{ $order->customer?->user?->name ?? 'Walk-in Customer' }}</td>
        </tr>
        @if($order->customer?->user?->address)
        <tr>
            <td colspan="2"><strong>Address:</strong> {{ $order->customer->user->address }}</td>
        </tr>
        @endif
        @if($order->customer?->gstin)
        <tr>
            <td colspan="2"><strong>GSTIN/UIN:</strong> {{ $order->customer->gstin }}</td>
        </tr>
        @endif
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="8%" align="left">Sl</th>
                <th width="48%" align="left">Description</th>
                <th width="12%" align="right">Qty</th>
                <th width="16%" align="right">Rate</th>
                <th width="16%" align="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $returnedBarcodes = \App\Models\POSReturnProduct::whereHas('returnHeader', function ($q) use ($order) {
                    $q->where('original_order_id', $order->id);
                })->pluck('barcode_number')->toArray();
            @endphp
            @foreach($order->products as $index => $product)
                @php
                    $lineNetPrice = $product->pivot->price; // Net unit rate after discounts
                    $lineTotal = $lineNetPrice * $product->pivot->quantity;
                    
                    $barcode = $product->pivot->barcode_number;
                    $isReturned = in_array($barcode, $returnedBarcodes);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        [NEW] {{ $product->name }}
                        @if($isReturned)
                            <span style="font-size: 8px; font-weight: 700; color: #dc3545; border: 1px solid #dc3545; padding: 0px 2px; border-radius: 2px; text-transform: uppercase; margin-left: 2px; display: inline-block;">Returned</span>
                        @endif
                        @if($product->pivot->color || $product->pivot->size)
                            <br><span style="font-size: 9px; color: #555;">({{ $product->pivot->color ?? '' }} / {{ $product->pivot->size ?? '' }})</span>
                        @endif
                    </td>
                    <td align="right">{{ $product->pivot->quantity }}</td>
                    <td align="right">{{ number_format($lineNetPrice, 2) }}</td>
                    <td align="right">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach

            @if($exchangeInfo && !empty($exchangeInfo['returned_items']))
                <tr>
                    <td colspan="5" class="bold" style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; font-size: 10px; padding: 4px 0; background: #fff5f5; text-transform: uppercase;">
                        Returned Items (Ref: #{{ $exchangeInfo['original_invoice_no'] }})
                    </td>
                </tr>
                @foreach($exchangeInfo['returned_items'] as $retIdx => $retItem)
                <tr style="color: #991b1b;">
                    <td>{{ $retIdx + 1 }}</td>
                    <td>
                        [RETURNED] {{ $retItem['name'] }}<br>
                        <span style="font-size: 8px; color: #777;">(Bc: {{ $retItem['barcode'] }})</span>
                    </td>
                    <td align="right">{{ $retItem['qty'] }}</td>
                    <td align="right">{{ number_format($retItem['rate'], 2) }}</td>
                    <td align="right">-{{ number_format($retItem['total'], 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="summary-table">
        @if($exchangeInfo)
        <tr>
            <td width="60%">New Items Subtotal:</td>
            <td width="40%" class="text-right">{{ number_format($itemsNetTotal, 2) }}</td>
        </tr>
        <tr>
            <td width="60%">Returned Credit (Ref #{{ $exchangeInfo['original_invoice_no'] }}):</td>
            <td width="40%" class="text-right" style="color: #dc3545; font-weight: bold;">-{{ number_format($exchangeInfo['returned_total'], 2) }}</td>
        </tr>
        @endif
        @if($order->coupon_discount > 0)
        <tr>
            <td width="60%">Total Discount:</td>
            <td width="40%" class="text-right">-{{ number_format($order->coupon_discount, 2) }}</td>
        </tr>
        @endif
        @foreach($order->vatTaxes as $tax)
        <tr>
            <td width="60%">{{ $tax->name }} Output:</td>
            <td width="40%" class="text-right">{{ number_format($tax->amount, 2) }}</td>
        </tr>
        @endforeach
        @if(abs($roundOff) > 0.001)
        <tr>
            <td width="60%">Round Off:</td>
            <td width="40%" class="text-right">{{ $roundOff > 0 ? '+' : '' }}{{ number_format($roundOff, 2) }}</td>
        </tr>
        @endif
        <tr class="bold">
            <td>Total Qty: {{ $order->products->sum('pivot.quantity') }}</td>
            <td class="text-right">Total: {{ number_format($order->payable_amount, 2) }}</td>
        </tr>
    </table>

    @if($order->vatTaxes->count() > 0)
        <div class="divider"></div>
        <table class="tax-table">
            <thead>
                <tr>
                    <th align="left">Tax Type</th>
                    <th align="right">Taxable Amt</th>
                    <th align="right">Tax Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->vatTaxes as $tax)
                    @php
                        // Calculate taxable base for this tax rate group
                        $taxableAmt = $tax->percentage > 0 ? ($tax->amount / ($tax->percentage / 100)) : 0;
                    @endphp
                    <tr>
                        <td>{{ $tax->name }} @ {{ $tax->percentage }}%</td>
                        <td align="right">{{ number_format($taxableAmt, 2) }}</td>
                        <td align="right">{{ number_format($tax->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bold">
                    <td style="border-top: 1px dashed #000;">Total</td>
                    <td align="right" style="border-top: 1px dashed #000;">
                        {{ number_format($order->vatTaxes->sum(fn($t) => $t->percentage > 0 ? ($t->amount / ($t->percentage / 100)) : 0), 2) }}
                    </td>
                    <td align="right" style="border-top: 1px dashed #000;">
                        {{ number_format($order->vatTaxes->sum('amount'), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="final-amount-box text-center">
        Total Final Amount: {{ number_format($order->payable_amount, 2) }}
    </div>

    @if($order->e_invoice_irn)
        <div class="e-invoice-section text-center">
            <div class="e-invoice-title">e-Invoice Details</div>
            <div align="left">
                <strong>IRN:</strong> {{ $order->e_invoice_irn }}<br>
                <strong>Ack No:</strong> {{ $order->e_invoice_ack_no }}<br>
                <strong>Ack Date:</strong> {{ $order->e_invoice_ack_date ? $order->e_invoice_ack_date->format('d-M-Y') : '' }}
            </div>
            
            @php
                $invoiceUrl = route('shop.pos.invoice', $order->uuid ?? $order->id);
                $qrCode = new \Endroid\QrCode\QrCode($invoiceUrl);
                $qrCode->setSize(120);
                $writer = new \Endroid\QrCode\Writer\SvgWriter();
                $qrCodeUri = $writer->write($qrCode)->getDataUri();
            @endphp
            
            <div class="qr-container">
                <img src="{{ $qrCodeUri }}" class="qr-image" alt="e-Invoice QR Code">
            </div>
        </div>
        <div class="divider"></div>
    @endif

    <div class="declaration">
        <strong>Declaration:</strong><br>
        We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
    </div>

    <div class="footer text-center bold">
        THANK YOU FOR SHOPPING WITH US<br>
        VISIT AGAIN | HAVE A NICE DAY
    </div>

    </div>
</body>
</html>
