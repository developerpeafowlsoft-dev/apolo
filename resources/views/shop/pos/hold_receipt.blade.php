<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hold Bill Receipt - {{ $hold->hold_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 80mm;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .header { margin-bottom: 10px; }
        .title { font-size: 14px; font-weight: bold; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .item-table th, .item-table td { font-size: 11px; padding: 2px 0; }
        .warning-box {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 15px;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header text-center">
        <span class="title">{{ $shop->name }}</span><br>
        <span>{{ $shop->address }}</span><br>
        <span>Phone: {{ $shop->phone }}</span>
    </div>

    <div class="border-top border-bottom">
        <strong>HOLD RECEIPT</strong><br>
        Hold No: {{ $hold->hold_no }}<br>
        Date: {{ $hold->created_at->format('Y-m-d h:i A') }}<br>
        Cashier: {{ $hold->cashier?->name }}<br>
        Customer: {{ $hold->customer?->user?->name ?? $hold->customer_name ?? 'Walk-in Customer' }}<br>
        Phone: {{ $hold->customer?->user?->phone ?? $hold->customer_phone ?? '-' }}
    </div>

    <table class="item-table">
        <thead>
            <tr class="border-bottom">
                <th align="left">Item Description</th>
                <th align="center">Qty</th>
                <th align="right">Price</th>
                <th align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hold->items as $item)
                <tr>
                    <td>
                        {{ $item->product?->name }}
                        @if($item->color || $item->size)
                            <br><small>({{ $item->color ?? '' }} / {{ $item->size ?? '' }})</small>
                        @endif
                    </td>
                    <td align="center">{{ $item->qty }}</td>
                    <td align="right">{{ number_format($item->rate, 2) }}</td>
                    <td align="right">{{ number_format($item->rate * $item->qty, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top text-right">
        Subtotal: {{ number_format($hold->subtotal, 2) }}<br>
        Discount: {{ number_format($hold->discount, 2) }}<br>
        Tax Amount: {{ number_format($hold->tax_amount, 2) }}<br>
        <span class="bold">Total Payable: {{ number_format($hold->payable_amount, 2) }}</span>
    </div>

    @if($hold->remarks)
        <div class="border-top" style="font-size: 11px;">
            Remarks: {{ $hold->remarks }}
        </div>
    @endif

    <div class="warning-box">
        *** TEMPORARY SUSPENDED BILL ***<br>
        NOT A VALID TAX INVOICE<br>
        STOCK AND ACCOUNTING LEDGERS NOT AFFECTED
    </div>

    <div class="text-center" style="margin-top: 15px; font-size: 10px;">
        Thank You!
    </div>

</body>
</html>
