@extends('layouts.app')
@section('header-title', __('Brand List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Brand List') }}
        </h4>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createBrand" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </button>
        </div>
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
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $key => $brand)
                            @php
                                $serial = $brands->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $brand->name }}</td>
                                <td>
                                    @if($brand->shop_id && $brand->shop_id != $rootShop?->id && $brand->shop)
                                        <span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 12px;">
                                            {{ $brand->shop->name }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2 py-1" style="font-size: 12px;">
                                            {{ __('Super Admin') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="{{ route('shop.brand.toggle', $brand->id) }}">
                                            <input type="checkbox" {{ $brand->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
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

    <!--=== Create Brand Modal ===-->
    <form action="{{ route('shop.brand.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="createBrand" tabindex="-1" aria-labelledby="createBrandLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createBrandLabel">
                            {{ __('Create Brand') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{ __('Enter Brand Name') }}" required />
                            @if(isset($errors) && $errors->has('name'))
                                <p class="text text-danger m-0">{{ $errors->first('name') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
