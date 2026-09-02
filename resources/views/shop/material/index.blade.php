@extends('layouts.app')
@section('header-title', __('Material List'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Material List') }}
        </h4>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createMaterial" class="btn py-2 btn-primary">
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
                        {{ __('Materials') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($materials as $key => $material)
                            @php
                                $serial = $materials->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $material->code }}</td>
                                <td>{{ $material->name }}</td>
                                <td>
                                    @if($material->shop_id && $material->shop_id != $rootShop?->id && $material->shop)
                                        <span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 12px;">
                                            {{ $material->shop->name }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2 py-1" style="font-size: 12px;">
                                            {{ __('Super Admin') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="{{ route('shop.material.toggle', $material->id) }}">
                                            <input type="checkbox" {{ $material->is_active ? 'checked' : '' }}>
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
            {{ $materials->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Create Material Modal ===-->
    <form action="{{ route('shop.material.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="createMaterial" tabindex="-1" aria-labelledby="createMaterialLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createMaterialLabel">
                            {{ __('Create Material') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                <span>{{ __('Code') }} <span class="text-danger">*</span></span>
                            </label>
                            <div class="input-group flex-nowrap">
                                <input type="text" class="form-control disabledCls" name="code" placeholder="{{ __('Code') }}" id="shortCode" value="" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);" required readonly>
                                <button class="btn btn-secondary" type="button" id="generateShortCode" onclick="generateCode()" data-bs-toggle="tooltip" title="{{ __('Generate Code') }}">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            @if(isset($errors) && $errors->has('code'))
                                <span class="text-danger small">{{ $errors->first('code') }}</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{ __('Enter Material Name') }}" required />
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

@push('scripts')
    <script>
        const codeInput = document.getElementById('shortCode');
        if (codeInput) {
            codeInput.value = Math.floor(Math.random() * 9000) + 1000;
        }

        const generateCode = () => {
            const codeInput = document.getElementById('shortCode');
            if (codeInput) {
                codeInput.value = Math.floor(Math.random() * 9000) + 1000;
            }
        }
    </script>
@endpush