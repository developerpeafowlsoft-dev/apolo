<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Note Receipt - #{{ $returnHeader->return_no }}</title>
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
        .title { font-size: 12px; font-weight: bold; margin: 8px 0; text-transform: uppercase; }
        
        .metadata-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .metadata-table td { font-size: 11px; padding: 1px 0; vertical-align: top; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .item-table th, .item-table td { font-size: 11px; padding: 3px 0; vertical-align: top; }
        .item-table th { border-bottom: 1px dashed #000; }
        
        .final-amount-box {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0;
            padding: 4px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .footer {
            margin-top: 15px;
            font-size: 10px;
            line-height: 1.3;
        }
        
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 10px;
                width: 80mm;
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
            <span>E-Mail: {{ $email }}</span><br>
        @endif
        <div class="divider"></div>
        <div class="title">CREDIT NOTE / RETURN RECEIPT</div>
        <div class="divider"></div>
    </div>

    <table class="metadata-table">
        <tr>
            <td width="40%"><strong>Credit Note No:</strong></td>
            <td width="60%">{{ $returnHeader->return_no }}</td>
        </tr>
        <tr>
            <td><strong>Return Date:</strong></td>
            <td>{{ $returnHeader->created_at->format('d-M-y h:i A') }}</td>
        </tr>
        <tr>
            <td><strong>Orig Invoice:</strong></td>
            <td>{{ $returnHeader->originalOrder->prefix ?? '' }}-{{ $returnHeader->originalOrder->order_code ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Party Name:</strong></td>
            <td>{{ $returnHeader->customer->user->name ?? 'Walk-in Customer' }}</td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="10%" align="left">Sl</th>
                <th width="45%" align="left">Description</th>
                <th width="15%" align="right">Qty</th>
                <th width="15%" align="right">Rate</th>
                <th width="15%" align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnHeader->products as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product->name ?? 'Product' }}<br>
                        <span style="font-size:9px; color:#555;">(Bc: {{ $item->barcode_number }})</span>
                    </td>
                    <td align="right">{{ $item->qty }}</td>
                    <td align="right">{{ number_format($item->rate, 2) }}</td>
                    <td align="right">{{ number_format($item->rate * $item->qty, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="final-amount-box text-center">
        Total Refund: ₹{{ number_format($returnHeader->total_amount, 2) }}
    </div>

    <table class="metadata-table" style="margin-top: 5px;">
        <tr>
            <td width="40%"><strong>Refund Method:</strong></td>
            <td width="60%" style="text-transform: uppercase;">{{ $returnHeader->payment_method === 'cash' ? 'Cash Refund' : 'Bank / UPI' }}</td>
        </tr>
        <tr>
            <td><strong>Cashier:</strong></td>
            <td>{{ $returnHeader->cashier->name ?? 'Cashier' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer text-center">
        <strong>THANK YOU</strong><br>
        RETURN / EXCHANGE TRANSACTION COMPLETE
    </div>
</div>

<script type="text/javascript">
    // Auto-printing disabled to support direct screen display of thermal layout
    // window.onload = function() {
    //     window.print();
    // }
</script>
</body>
</html>
