@extends('layouts.app')
@section('header-title', __('Color List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Color List') }}
        </h4>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createColor" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </button>
        </div>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Color') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($colors as $key => $color)
                            @php
                                $serial = $colors->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $color->name }}</td>
                                <td>
                                    <div style="width: 42px; height: 28px; border-radius: 4px; background: {{ $color->color_code }}; border: 1px solid #ddd;">
                                    </div>
                                </td>
                                <td>
                                    @if($color->shop_id && $color->shop_id != $rootShop?->id && $color->shop)
                                        <span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 12px;">
                                            {{ $color->shop->name }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2 py-1" style="font-size: 12px;">
                                            {{ __('Super Admin') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="{{ route('shop.color.toggle', $color->id) }}">
                                            <input type="checkbox" {{ $color->is_active ? 'checked' : '' }}>
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
            {{ $colors->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Create Color Modal ===-->
    <form action="{{ route('shop.color.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="createColor" tabindex="-1" aria-labelledby="createColorLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createColorLabel">
                            {{ __('Create New Color') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                {{ __('Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{ __('Enter Color Name') }}" required />
                            @if(isset($errors) && $errors->has('name'))
                                <p class="text text-danger m-0">{{ $errors->first('name') }}</p>
                            @endif
                        </div>

                        <div class="mb-3 d-flex align-items-center gap-3">
                            <label for="color_code" class="form-label m-0">
                                {{ __('Select Color') }} <span class="text-danger">*</span>
                            </label>
                            <input type="color" id="color_code" name="color_code" value="#000000" style="width: 120px; height: 40px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;"/>
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
