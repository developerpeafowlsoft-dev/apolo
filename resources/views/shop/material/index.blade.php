@extends('layouts.app')
@section('header-title', __('Material List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Material List') }}
        </h4>
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
                            <th>{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($materials as $key => $material)
                            @php
                                $serial = $materials->firstItem() + $key;
                                $userShopId = $currentShopId ?? (auth()->user()?->shop?->id ?? auth()->user()?->myShop?->id ?? auth()->user()?->shop_id);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $material->code }}</td>
                                <td>{{ $material->name }}</td>
                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0)">
                                            <input type="checkbox" {{ $material->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                <td class="text-center">
                                    @if($material->isOwnedByShop($userShopId))
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary circleIcon btn-sm" onclick="openUpdateModal({{ $material }})" title="{{ __('Edit') }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </button>

                                            <button type="button" class="btn btn-outline-danger circleIcon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $material->id }}" title="{{ __('Delete') }}">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" />
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $material->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>{{ __('Are you sure you want to delete material') }} <strong>{{ $material->name }}</strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <form action="{{ route('shop.material.destroy', $material->id) }}" method="POST" class="d-inline">
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
            {{ $materials->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Edit Material Modal ===-->
    <form action="" id="formEditMaterial" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateMaterial" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Edit Material') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align: left">

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                <span>
                                    {{ __('Code') }}
                                    <span class="text-danger">*</span>
                                </span>
                                </div>
                            </label>
                            <div class="input-group flex-nowrap">
                                <input type="text" class="form-control disabledCls @error('code') is-invalid @enderror" placeholder="Code" name="code" id="editCode" value="{{ old('code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);" required="true" readonly>
                            </div>
                            @error('code')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="editName" class="form-label">{{ __('Name') }} *</label>
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

@push('scripts')
    <script>
        const openUpdateModal = (material) => {
            $("#editName").val(material.name);
            $("#editCode").val(material.code);

            $("#formEditMaterial").attr('action', `{{ route('shop.material.update', ':id') }}`.replace(':id', material.id));

            $("#updateMaterial").modal('show');
        }
    </script>
@endpush
@endsection