@extends('layouts.app')
@section('header-title', __('Barcode Print'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Barcode Print')}}
        </h4>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <form id="barcode-generate-form" action="{{route('shop.productBarcodeGenerateMultiple.generateMultiple')}}" method="POST" target="_blank">
                        @csrf
                        <div class="button-container text-end">
                            <button id="generate-btn" type="submit" class="btn btn-sm btn-success mb-3" disabled>
                                {{__('Barcode Print')}}
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table border-left-right table-responsive-md">
                                <thead>
                                <tr>
                                    <th class="text-center">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </th>
                                    <th class="text-center">{{ __('SL') }}</th>
                                    <th>{{ __('Voucher No.') }}</th>
                                    <th>{{ __('Barcode') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Design No.') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('MRP') }}</th>
                                    <th>{{ __('Party Name') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    @hasPermission('shop.inwardProduct.index')
                                    <th class="text-center">{{ __('Action') }}</th>
                                    @endhasPermission
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($barcodeDatas as $key => $barcodeData)
                                    @php
                                        $serial = $barcodeDatas->firstItem() + $key;
                                    @endphp
                                    <tr style="{{ $barcodeData->is_sold == 1 ? 'background-color: #fafafa; opacity: 0.85;' : '' }}">
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="barcode_ids[]"
                                                   value="{{ $barcodeData->id }}"
                                                   class="form-check-input barcode-checkbox"
                                                   @if($barcodeData->is_sold == 1) disabled @endif>
                                        </td>
                                        <td class="text-center fw-semibold text-secondary">{{ $serial }}</td>
                                        <td class="font-monospace">{{ $barcodeData->inwardInvoice?->inward_voucher_no ?? '' }}</td>
                                        <td class="font-monospace fw-bold text-dark">{{ $barcodeData->barcode_number ?? '' }}</td>
                                        <td>{{ $barcodeData->inwardProduct?->products?->name ?? '' }}</td>
                                        <td><span class="border font-monospace px-1.5 py-0.5 rounded text-dark" style="background-color: #f1f5f9; font-size: 11.5px; font-weight: 600;">{{ $barcodeData->inwardProduct?->designMaster?->design_number ?? '' }}</span></td>
                                        <td>
                                            @foreach(optional($barcodeData->inwardProduct)->colors ?? [] as $color)
                                                <span class="border px-1.5 py-0.5 rounded mr-1" style="font-size:11px; background-color: #f1f5f9; color: #475569 !important; font-weight: 600; display: inline-block;">
                                                    {{ $color->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if(optional($barcodeData->inwardProduct)->sizes?->isNotEmpty())
                                                @foreach($barcodeData->inwardProduct->sizes as $size)
                                                    <span class="border px-1.5 py-0.5 rounded mr-1" style="font-size:11px; background-color: #f1f5f9; color: #1e293b !important; font-weight: 600; display: inline-block;">
                                                        {{ $size->name }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="font-monospace text-center">{{ $barcodeData->inwardProduct?->quantity ?? '' }}</td>
                                        <td class="font-monospace fw-semibold">₹{{ number_format($barcodeData->inwardProduct?->mrp ?? 0, 2) }}</td>
                                        <td style="font-size: 12px; color: #475569;">{{ $barcodeData->inwardInvoice?->partyCode?->accountName ?? '' }}</td>
                                        <td class="text-center">
                                            @if($barcodeData->is_sold == 1)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded" style="font-size: 11px;">
                                                    <i class="bi bi-x-circle-fill me-1"></i>Sold / Used
                                                </span>
                                                @if($barcodeData->orderProduct?->order)
                                                    <div class="mt-1 font-monospace text-start mx-auto" style="font-size: 10px; color: #64748b; line-height: 1.45; width: max-content;">
                                                        <div><span class="fw-semibold text-dark">Inv:</span> {{ $barcodeData->orderProduct->order->order_code }}</div>
                                                        <div><span class="fw-semibold text-dark">Date:</span> {{ $barcodeData->orderProduct->order->created_at->format('d-m-Y H:i') }}</div>
                                                    </div>
                                                @endif
                                            @elseif($barcodeData->is_printed == 1)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded" style="font-size: 11px;">
                                                    <i class="bi bi-printer-fill me-1"></i>Printed
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded" style="font-size: 11px;">
                                                    <i class="bi bi-hourglass-split me-1"></i>New (Pending)
                                                </span>
                                            @endif
                                        </td>
                                        @hasPermission('shop.inwardProduct.index')
                                        <td class="text-center">
                                            @if($barcodeData->is_sold == 1)
                                                <button type="button" class="btn btn-outline-secondary circleIcon btn-sm" disabled data-bs-toggle="tooltip" data-bs-title="Sold items cannot be reprinted">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('shop.productBarcodeGenerate.generate', $barcodeData->id) }}" class="btn btn-outline-primary circleIcon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print / Reprint this single barcode" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            @endif
                                        </td>
                                        @endhasPermission

                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="my-3">
                        {{ $barcodeDatas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>

        $(document).ready(function () {
            // Select this specific form
            var $form = $('#barcode-generate-form');

            // Remove any previous submit handlers
            $form.off('submit');

            // Add a clean submit handler (no spinner / disable)
            $form.on('submit', function(e) {
                return true; // normal form submission
            });

            // Select the Generate button
            var $button = $('#generate-btn');

            // Function to toggle button state based on checked checkboxes
            function toggleButtonState() {
                var anyChecked = $('.barcode-checkbox:checked').length > 0;
                $button.prop('disabled', !anyChecked);
            }

            // Run on page load (in case some checkboxes are pre-checked)
            toggleButtonState();

            // Run when any individual checkbox changes
            $('.barcode-checkbox').on('change', toggleButtonState);

            // Select All checkbox logic (only checks enabled, unsold barcodes)
            $('#selectAll').on('click', function () {
                var checked = $(this).is(':checked');
                $('.barcode-checkbox:not(:disabled)').prop('checked', checked);
                toggleButtonState();
            });
        });



    </script>
@endpush