<div class="table-responsive">
    <table class="table border-left-right table-responsive-md">
        <thead>
        <tr>
            <th class="text-center">{{ __('SL') }}</th>
            <th>{{ __('Item No / ID') }}</th>
            <th>{{ __('Item Name') }}</th>
            <th>{{ __('HSN Code') }}</th>
            <th>{{ __('Tax Type') }}</th>
            <th>{{ __('Material') }}</th>
            <th>{{ __('Salesman') }}</th>
            <th>{{ __('Commission') }}</th>

            <th>{{ __('Is Online Product') }}</th>
            {{--                                @hasPermission('shop.itemMaster.onlineProductToggle')--}}
            {{--                                <th>{{ __('Status') }}</th>--}}
            {{--                                @endhasPermission--}}
            @hasPermission('shop.itemMaster.edit')
            <th class="text-center">{{ __('Action') }}</th>
            @endhasPermission
        </tr>
        </thead>
        <tbody>
        @forelse($itemMasters as $key => $itemMaster)
            @php
                $serial = $itemMasters->firstItem() + $key;
            @endphp
            <tr>
                <td class="text-center">{{ $serial }}</td>
                <td>
                    <span class="badge bg-light-primary text-primary fw-bold font-monospace fs-6">
                        {{ $itemMaster->code ?: $itemMaster->id }}
                    </span>
                </td>
                <td>
                    {{ $itemMaster->name ?? '' }}
                    <div class="text-muted">
                        <span class="badge badge-dark rounded-start-5">{{ $itemMaster->categories->pluck('name')->join(', ') }}</span>
                        <span class="badge badge-info rounded-end-pill"> {{ $itemMaster->brand->name ?? '' }}</span>

                    </div>
                </td>
                <td>{{ $itemMaster->hsnMaster->hsn_code ?? '-' }}</td>
                <td>
                    {{ $itemMaster->vatTax->name ?? '-' }}
                </td>
                <td>
                    {{ $itemMaster->material->name ?? '-' }}
                </td>
                <td>
                    {{ $itemMaster->salesman->name ?? '-' }}
                </td>
                <td>

                    @if(!empty($itemMaster->salesman->name))
                        @if($itemMaster->commission_type === '1')
                            {{$itemMaster->salesman_comm}} (%)
                        @elseif($itemMaster->commission_type === '2')
                            {{$itemMaster->salesman_comm_amt}} (₹)
                        @endif
                    @else
                        -
                    @endif

                </td>

                @hasPermission('shop.itemMaster.onlineProductToggle')
                <td>
                    <label class="switch mb-0">
                        <a href="{{ route('shop.itemMaster.onlineProductToggle', $itemMaster->id) }}" class="toggle-status-link">
                            <input type="checkbox" {{ $itemMaster->is_online_product ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </a>
                    </label>
                </td>
                @endhasPermission
                <td class="text-center">
                    <div class="d-flex gap-3 justify-content-center">
                        @hasPermission('shop.itemMaster.edit')
                        <a href="javascript:void(0)" data-id="{{$itemMaster->id}}" class="btn btn-outline-primary btn-sm circleIcon editData"
                        >
                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                        </a>
                        @endhasPermission
                        @hasPermission('shop.itemMaster.destroy')
                        <a href="{{ route('shop.itemMaster.destroy', $itemMaster->id) }}" class="btn btn-outline-danger circleIcon deleteConfirm" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Delete">
                            <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="trash" loading="lazy">
                        </a>
                        @endhasPermission
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="my-3">
    {{ $itemMasters->links() }}
</div>