@extends('layouts.app')
@section('header-title', __('Color List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Color List') }}
            @if(isset($search) && $search)
                <span class="badge bg-primary fs-6 ms-2">{{ __('Found') }}: {{ $colors->total() }}</span>
            @endif
        </h4>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="{{ route('shop.color.index') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('Search color name or hex...') }}" value="{{ request('search') }}" style="min-width: 220px;">
                    @if(request('search'))
                        <a href="{{ route('shop.color.index') }}" class="btn btn-outline-secondary border-start-0" title="{{ __('Clear Search') }}">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Color') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($colors as $key => $color)
                            @php
                                $serial = $colors->firstItem() + $key;
                                $userShopId = $currentShopId ?? (auth()->user()?->shop?->id ?? auth()->user()?->myShop?->id ?? auth()->user()?->shop_id);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $color->name }}</td>

                                <td>
                                    <div
                                        style="width: 42px; height: 28px; border-radius: 4px; background: {{ $color->color_code }}">
                                    </div>
                                </td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0)">
                                            <input type="checkbox" {{ $color->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                <td class="text-center">
                                    @if($color->isOwnedByShop($userShopId))
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary circleIcon btn-sm" onclick="openColorUpdateModal({{ $color }})" title="{{ __('Edit') }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </button>

                                            <button type="button" class="btn btn-outline-danger circleIcon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $color->id }}" title="{{ __('Delete') }}">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" />
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $color->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>{{ __('Are you sure you want to delete color') }} <strong>{{ $color->name }}</strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <form action="{{ route('shop.color.destroy', $color->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
            </div>
        </div>

        <div class="my-3">
            {{ $colors->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Edit Color Modal ===-->
    <form action="" id="updateColor" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Edit Color') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align: left">

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="updateName" name="name"
                                placeholder="{{ __('Enter Name') }}" value="" required />
                            @error('name')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="color_code" class="form-label">{{ __('Color Code') }} <span class="text-danger">*</span></label>
                            <input type="color" id="updateColorCode" name="color_code" style="width: 120px;height: 40px"/>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Update') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    const openColorUpdateModal = (color) => {
        $("#updateName").val(color.name);
        $("#updateColorCode").val(color.color_code);
        $("#updateColor").attr('action', `{{ route('shop.color.update', ':id') }}`.replace(':id', color.id));
        $("#updateBrand").modal('show');
    }
</script>
@endpush
