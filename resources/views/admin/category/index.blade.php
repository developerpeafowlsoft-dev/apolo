@extends('layouts.app')

@section('header-title', __('Categories'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Category List') }}
            @if(request('search'))
                <span class="badge bg-primary fs-6 ms-2">{{ __('Found') }}: {{ $categories->total() }}</span>
            @endif
        </h4>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <form action="{{ route('admin.category.index') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('Search category...') }}" value="{{ request('search') }}" style="min-width: 200px;">
                    @if(request('search'))
                        <a href="{{ route('admin.category.index') }}" class="btn btn-outline-secondary border-start-0" title="{{ __('Clear Search') }}">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </form>

            @hasPermission('admin.category.create')
            <a href="{{ route('admin.category.create') }}" class="btn py-2 btn-primary">
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
                        {{__('Categories')}}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Thumbnail') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th class="text-center">{{ __('Inward Product') }}</th>
                                <th class="text-center">{{ __('Online Product') }}</th>
                                <th class="text-center">{{ __('Show in Hero') }}</th>
                                @hasPermission('admin.category.toggle')
                                <th class="text-center">{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('admin.category.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $key => $category)
                            @php
                                $serial = $categories->firstItem() + $key;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $serial }}</td>

                                <td>
                                    <img src="{{ $category->thumbnail }}" width="50" height="50" class="rounded object-fit-cover">
                                </td>

                                <td class="fw-semibold">{{ $category->name }}</td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-user me-1 text-secondary"></i>
                                        {{ $category->creator?->name ?? __('Super Admin') }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fs-6 fw-bold">
                                        {{ $category->inward_products_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-1.5 fs-6 fw-bold">
                                        {{ $category->online_products_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <label class="switch mb-0">
                                        <a href="{{ route('admin.category.hero-toggle', $category->id) }}" title="{{ __('Toggle Hero Section Display') }}">
                                            <input type="checkbox" {{ $category->show_in_hero ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>

                                @hasPermission('admin.category.toggle')
                                <td class="text-center">
                                    <label class="switch mb-0">
                                        <a href="{{ route('admin.category.toggle', $category->id) }}" title="{{ __('Toggle Status') }}">
                                            <input type="checkbox" {{ $category->status ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </a>
                                    </label>
                                </td>
                                @endhasPermission

                                @hasPermission('admin.category.edit')
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.category.edit', $category->id) }}" class="btn btn-outline-primary circleIcon" title="{{ __('Edit') }}">
                                            <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                        </a>
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
            {{ $categories->withQueryString()->links() }}
        </div>

    </div>
@endsection
