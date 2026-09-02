<!DOCTYPE html>
<html>
<head>
    <title>Print Barcode</title>

    <style>
        /* IMPORTANT: CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            /* Exact size in mm (printer ke liye) */
            size: 76mm 25mm; /* 7.4cm = 74mm, 2.5cm = 25mm */
            margin: 0;
        }

        @media print {
            html, body {
                margin: 0;
                padding: 0;
                width: 76mm;
                height: 25mm;
            }
            .mrp-box {
                background: black !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        body {
            margin: 0;
            padding: 0;
            width: 76mm;
            font-family: Arial, Helvetica, sans-serif;
            background: white;
        }

        .page {
            display: flex;
            flex-wrap: wrap;
            width: 76mm;
            margin: 0;
            padding: 0;
            background: white;
        }

        /* Exact 37mm x 25mm per label (3.7cm x 2.5cm) */
        .label {
            width: 37mm;
            height: 25mm;
            /*padding: 0.5mm 1mm;*/
            margin: 0;
            float: left;
            page-break-inside: avoid;
            overflow: hidden;
            font-size: 8px;
            line-height: 1.2;
            border: none;
            background: white;
            /* Debugging ke liye border hataye */
            /* border: 0.1px solid #ccc; */
        }

        .label:nth-child(odd){
            /*padding: 2.5mm 4mm 2.5mm 4mm;*/
            padding: 2mm 4mm 2mm 2mm;
        }

        .label:nth-child(even){
            /*padding: 2.5mm 4mm 2.5mm 4mm;*/
            padding: 2mm 1mm 2mm 5mm;
        }

        /* Clear after every 2 labels */
        .clearfix {
            clear: both;
            height: 0;
            width: 100%;
        }

        .product-name {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 11px;
            width: 100%;
        }

        .size-code-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8.5px;
            font-weight: bold;
            margin: 1px 0;
            white-space: nowrap;
            height: 12px;
            width: 100%;
        }

        .size-part {
            display: flex;
            gap: 2px;
            white-space: nowrap;
        }

        .code-part {
            font-weight: bold;
            white-space: nowrap;
        }

        .mrp-box {
            background: black;
            color: white;
            font-weight: bold;
            font-size: 11px;
            padding: 1px 0;
            text-align: center;
            margin: 2px 0;
            letter-spacing: 0.5px;
            height: 14px;
            line-height: 14px;
            width: 100%;
        }

        .barcode-img {
            width: 100%;
            height: 9mm; /* Fixed height in mm */
            object-fit: contain;
            margin: 1px 0;
            display: block;
        }

        .barcode-row {
            display: flex;
            justify-content: space-between;
            font-size: 7.5px;
            font-weight: bold;
            margin-top: 1px;
            white-space: nowrap;
            height: 10px;
            line-height: 10px;
            width: 100%;
        }

        .barcode-number {
            letter-spacing: -0.2px;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }

        .assorte-text {
            color: #333;
            max-width: 40%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Fix for half mm issues */
        .label-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
            justify-content: space-between;
        }
    </style>
</head>
<body onload="window.print()">

<div class="page">
        <div class="label">
            <div class="label-content">
                <!-- Product Name -->
                <div class="product-name">
                    {{ Str::limit($barcodeGenerate->inwardProduct?->products?->name ?? '-', 19) }}
                </div>

                <!-- Size and Code Row -->
                <div class="size-code-row">
                    <span class="size-part">
                        <span>
                            @if(optional($barcodeGenerate->inwardProduct)->sizes?->isNotEmpty())
                                {{ $barcodeGenerate->inwardProduct->sizes->pluck('name')->implode(', ') }}
                            @endif
                        </span>
                    </span>
                    <span class="code-part">
                        {{ $encodedPrice }}
                    </span>
                </div>

                <!-- MRP Box -->
                <div class="mrp-box">
                    {{__('Rs')}} {{ number_format($barcodeGenerate->inwardProduct?->mrp, 2) }}
                </div>

                <!-- Barcode Image -->
                <img src="data:image/png;base64,{{ $barcodeImage }}" class="barcode-img" alt="barcode">

                <!-- Barcode Number and ASSORTE -->
                <div class="barcode-row">
                    <span class="barcode-number">
                        {{ $barcodeGenerate->barcode_number }}
                    </span>
                    <span class="assorte-text">
                        {{ $barcodeGenerate->inwardProduct->colors->pluck('name')->implode(', ') }}
                    </span>
                </div>
            </div>
        </div>
</div>

<script>
    window.onload = function() {
        // Force reflow for accurate sizing
        window.document.body.style.display = 'none';
        window.document.body.offsetHeight; // reflow
        window.document.body.style.display = '';

        // Print with delay
        setTimeout(function() {
            window.print();
        }, 1000);

        // Close after print
        // setTimeout(function() {
        //     window.close();
        // }, 5000);
    }
</script>

</body>
</html>