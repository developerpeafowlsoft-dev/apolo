@extends('layouts.app')
@section('header-title', __('Brand List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Brand List') }}
        </h4>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">
                <div class="cardTitleBox">
                    <h5 class="card-title chartTitle">
                        {{ __('Brands') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $key => $brand)
                            @php
                                $serial = $brands->firstItem() + $key;
                                $userShopId = $currentShopId ?? (auth()->user()?->shop?->id ?? auth()->user()?->myShop?->id ?? auth()->user()?->shop_id);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $brand->name }}</td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0)">
                                            <input type="checkbox" {{ $brand->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                <td class="text-center">
                                    @if($brand->isOwnedByShop($userShopId))
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm circleIcon"
                                                onclick="openUpdateModal({{ $brand }})" title="{{ __('Edit') }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </button>

                                            <button type="button" class="btn btn-outline-danger circleIcon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $brand->id }}" title="{{ __('Delete') }}">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" />
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $brand->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>{{ __('Are you sure you want to delete brand') }} <strong>{{ $brand->name }}</strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <form action="{{ route('shop.brand.destroy', $brand->id) }}" method="POST" class="d-inline">
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
            {{ $brands->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Edit Brand Modal ===-->
    <form action="" id="formEditBrand" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Edit Brand') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align: left">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} *</label>
                            <input type="text" class="form-control" id="editName" name="name"
                                placeholder="Enter Name" value="" required />
                            @error('name')
                                <p class="text text-danger m-0">{{ $message }}</p>
                            @enderror
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
        const openUpdateModal = (brand) => {
            $("#editName").val(brand.name);
            $("#formEditBrand").attr('action', `{{ route('shop.brand.update', ':id') }}`.replace(':id', brand.id));

            $("#updateBrand").modal('show');
        }
    </script>
@endpush
