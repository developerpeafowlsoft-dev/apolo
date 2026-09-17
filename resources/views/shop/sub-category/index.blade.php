@extends('layouts.app')

@section('header-title', __('Sub Categories'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Sub Categories') }}
            @if(request('search'))
                <span class="badge bg-primary fs-6 ms-2">{{ __('Found') }}: {{ $subCategories->total() }}</span>
            @endif
        </h4>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <form action="{{ route('shop.subcategory.index') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('Search subcategory...') }}" value="{{ request('search') }}" style="min-width: 200px;">
                    @if(request('search'))
                        <a href="{{ route('shop.subcategory.index') }}" class="btn btn-outline-secondary border-start-0" title="{{ __('Clear Search') }}">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </form>

            @hasPermission('shop.subcategory.create')
            <a href="{{ route('shop.subcategory.create') }}" class="btn py-2 btn-primary">
                <i class="fa fa-plus-circle"></i>
                {{__('Create New')}}
            </a>
            @endhasPermission
        </div>
    </div>

    <div class="container-fluid mt-3">

        <div class="mb-3 card">
            <div class="card-body">
                <div class="cardTitleBox">
                    <h5 class="card-title chartTitle">
                        {{ __('Sub Categories') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Thumbnail') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th class="text-center">{{ __('Inward Product') }}</th>
                                <th class="text-center">{{ __('Online Product') }}</th>
                                @hasPermission('shop.subcategory.toggle')
                                <th class="text-center">{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.subcategory.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($subCategories as $key => $subCategory)
                            @php
                                $serial = $subCategories->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>

                                <td>
                                    <img src="{{ $subCategory->thumbnail }}" width="50" height="50" class="rounded object-fit-cover">
                                </td>
                                <td>
                                    @forelse ($subCategory->categories as $category)
                                        <span class="badge rounded-pill text-bg-primary me-1">{{ $category->name }}</span>
                                    @empty
                                        <span class="text-muted">N/A</span>
                                    @endforelse
                                </td>

                                <td class="fw-semibold">{{ $subCategory->name }}</td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-user me-1 text-secondary"></i>
                                        {{ $subCategory->creator?->name ?? __('Super Admin') }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fs-6 fw-bold">
                                        {{ $subCategory->inward_products_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-1.5 fs-6 fw-bold">
                                        {{ $subCategory->online_products_count ?? 0 }}
                                    </span>
                                </td>

                                @hasPermission('shop.subcategory.toggle')
                                <td class="text-center">
                                    <label class="switch mb-0">
                                        <a href="{{ route('shop.subcategory.toggle', $subCategory->id) }}" title="{{ __('Toggle Status') }}">
                                            <input type="checkbox" {{ $subCategory->is_active ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                @endhasPermission

                                @hasPermission('shop.subcategory.edit')
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('shop.subcategory.edit', $subCategory->id) }}" class="btn btn-outline-primary circleIcon" title="{{ __('Edit') }}">
                                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                        </a>
                                        <button type="button" class="btn btn-outline-danger circleIcon" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $subCategory->id }}" title="{{ __('Delete') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{{ $subCategory->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>{{ __('Are you sure you want to delete subcategory') }} <strong>{{ $subCategory->name }}</strong>?</p>
                                                    @if(($subCategory->inward_products_count ?? 0) > 0)
                                                        <div class="alert alert-warning py-2 mb-0">
                                                            <i class="fa fa-exclamation-triangle me-1"></i>
                                                            {{ __('This subcategory has') }} {{ $subCategory->inward_products_count }} {{ __('products associated with it.') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <form action="{{ route('shop.subcategory.destroy', $subCategory->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endhasPermission
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
            {{ $subCategories->withQueryString()->links() }}
        </div>

    </div>
@endsection
