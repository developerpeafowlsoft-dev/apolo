@extends('layouts.app')

@section('header-title', __('Size List'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">

        <h4>
            {{ __('Size List') }}
        </h4>

        @hasPermission('admin.size.create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#createBrand" class="btn py-2 btn-primary">
            <i class="fa fa-plus-circle"></i>
            {{ __('Create New') }}
        </button>
        @endhasPermission
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
                                @hasPermission('admin.size.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($sizes as $key => $size)
                            @php
                                $serial = $sizes->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $size->name }}</td>

                                @hasPermission('admin.size.toggle')
                                <td>
                                    <label class="switch mb-0">
                                        <a href="{{ route('admin.size.toggle', $size->id) }}">
                                            <input type="checkbox" {{ $size->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                @endhasPermission

                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        @hasPermission('admin.size.edit')
                                        <button type="button" class="btn btn-outline-primary btn-sm circleIcon" onclick="openColorUpdateModal({{ $size }})" title="{{ __('Edit') }}">
                                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy"/>
                                        </button>
                                        @endhasPermission

                                        @hasPermission('admin.size.destroy')
                                        <button type="button" class="btn btn-outline-danger circleIcon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $size->id }}" title="{{ __('Delete') }}">
                                            <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" />
                                        </button>
                                        @endhasPermission
                                    </div>

                                    @hasPermission('admin.size.destroy')
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{{ $size->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>{{ __('Are you sure you want to delete size') }} <strong>{{ $size->name }}</strong>?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <form action="{{ route('admin.size.destroy', $size->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endhasPermission
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
            {{ $sizes->withQueryString()->links() }}
        </div>

    </div>


    <!--=== Create Color Modal ===-->
    <form action="{{ route('admin.size.store') }}" method="POST">
        @csrf
        <div class="modal fade" id="createBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Create Size') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                {{ __('Name') }}
                                <small class="text-danger">*</small>
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{__('Name') }}" required />
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
                            {{ __('Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!--=== update color Modal ===-->
    <form action="" id="updateColor" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="updateBrand" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Update Size') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="updateName" class="form-label">
                                {{ __('Name') }}
                                <small class="text-danger">*</small>
                            </label>
                            <input type="text" class="form-control" id="updateName" name="name"
                                placeholder="{{__('Name') }}" required value="" />
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
    const openColorUpdateModal = (size) => {

        $("#updateName").val(size.name);
        $("#updateColor").attr('action', `{{ route('admin.size.update', ':id') }}`.replace(':id', size.id));

        $("#updateBrand").modal('show');
    }
</script>

@endpush
