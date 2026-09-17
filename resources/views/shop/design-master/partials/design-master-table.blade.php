<div class="table-responsive">
    <table class="table border-left-right table-responsive-md">
        <thead>
        <tr>
            <th class="text-center">{{ __('SL') }}</th>
            <th>{{ __('Design Number') }}</th>
            <th>{{ __('Item Name') }}</th>
            <th>{{ __('MRP') }}</th>
            <th>{{ __('Qty') }}</th>
            <th>{{ __('Min Stock') }}</th>
            <th>{{ __('Max Stock') }}</th>
            <th>{{ __('Account') }}</th>

            @hasPermission('shop.designMaster.toggle')
            <th>{{ __('Status') }}</th>
            @endhasPermission

            @hasPermission('shop.designMaster.edit')
            <th class="text-center">{{ __('Action') }}</th>
            @endhasPermission
        </tr>
        </thead>
        <tbody>
        @forelse($designMasters as $key => $designMaster)
            @php
                $serial = $designMasters->firstItem() + $key;
            @endphp
            <tr>
                <td class="text-center">{{ $serial }}</td>
                <td>
                    {{ $designMaster->design_number ?? '' }}
                </td>
                <td>{{ $designMaster->products->name ?? '' }}</td>
                <td>
                    {{ ($designMaster->mrp ?? '') }}
                </td>
                <td>
                    {{ ($designMaster->quantity ?? '') }}
                </td>
                <td>
                    {{ ($designMaster->min_stock ?? '') }}
                </td>
                <td>
                    {{ ($designMaster->max_stock ?? '') }}
                </td>
                <td>
                    {{ $designMaster->accountMasters?->accountName ?? ($designMaster->account_master_name ?: '-') }}
                    @if($designMaster->accountMasters?->accountshortcode)
                        <div class="text-muted">
                            <span class="badge badge-info rounded-end-pill">{{ $designMaster->accountMasters->accountshortcode }}</span>
                        </div>
                    @endif
                </td>

                @hasPermission('shop.designMaster.toggle')
                <td>
{{--                    <label class="switch mb-0">--}}
{{--                        <a href="{{ route('shop.designMaster.toggle', $designMaster->id) }}" class="toggle-status-link">--}}
{{--                            <input type="checkbox" {{ $designMaster->is_active ? 'checked' : '' }}>--}}
{{--                            <span class="slider round"></span>--}}
{{--                        </a>--}}
{{--                    </label>--}}
                    <label class="switch mb-0">
                        <input type="checkbox" class="toggle-status" data-id="{{ $designMaster->id }}" {{ $designMaster->is_active ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </td>
                @endhasPermission
                <td class="text-center">
                    <div class="d-flex gap-3 justify-content-center">
                        @hasPermission('shop.designMaster.edit')
                        <a href="javascript:void(0)" data-id="{{$designMaster->id}}" class="btn btn-outline-primary btn-sm circleIcon editData"
                        >
                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                        </a>
                        @endhasPermission
                        @hasPermission('shop.designMaster.destroy')
                        <a href="{{ route('shop.designMaster.destroy', $designMaster->id) }}" class="btn btn-outline-danger circleIcon deleteConfirm" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Delete">
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
    {{ $designMasters->links() }}
</div>