@extends('layouts.app')
@section('header-title', __('Unit List'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Unit List') }}
        </h4>
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
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($units as $key => $unit)
                            @php
                                $serial = $units->firstItem() + $key;
                                $userShopId = $currentShopId ?? (auth()->user()?->shop?->id ?? auth()->user()?->myShop?->id ?? auth()->user()?->shop_id);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $unit->name }}</td>

                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0)">
                                            <input type="checkbox" {{ $unit->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                <td class="text-center">
                                    @if($unit->isOwnedByShop($userShopId))
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm circleIcon"
                                                onclick="openUnitUpdateModal({{ $unit }})" title="{{ __('Edit') }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy"/>
                                            </button>

                                            <button type="button" class="btn btn-outline-danger circleIcon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $unit->id }}" title="{{ __('Delete') }}">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" />
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>{{ __('Are you sure you want to delete unit') }} <strong>{{ $unit->name }}</strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <form action="{{ route('shop.unit.destroy', $unit->id) }}" method="POST" class="d-inline">
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
            {{ $units->withQueryString()->links() }}
        </div>

    </div>

    <!--=== update unit Modal ===-->
    <form action="" id="updateunit" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Update Unit') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="updateName" class="form-label">
                                {{ __('Name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="updateName" name="name"
                                placeholder="{{ __('Name') }}" required value="" />
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
        const openUnitUpdateModal = (unit) => {
            $("#updateName").val(unit.name);
            $("#updateunit").attr('action', `{{ route('shop.unit.update', ':id') }}`.replace(':id', unit.id));
            $("#updateBrand").modal('show');
        }
    </script>
@endpush
