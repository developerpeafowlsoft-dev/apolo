@extends('layouts.app')
@section('header-title', __('Salesman'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Salesman')}}
        </h4>
        <div>
            <a href="{{route('shop.salesman.create')}}" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table border-left-right table-responsive-md">
                            <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Profile') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Gender') }}</th>
                                <th>{{ __('Date of Birth') }}</th>
                                @hasPermission('shop.salesman.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.salesman.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($salesmans as $key => $salesman)
                                @php
                                    $serial = $salesmans->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td><img src="{{ $salesman->thumbnail }}" width="50"></td>
                                    <td>{{ ($salesman->name ?? '') . ' ' . ($salesman->last_name ?? '') }}</td>
                                    <td>
                                        {{ $salesman->phone ?? '--' }}
                                    </td>
                                    <td>
                                        {{ $salesman->email ?? '--' }}
                                    </td>

                                    <td>
                                        {{ ucfirst($salesman->gender) ?? '--' }}
                                    </td>

                                    <td>
                                        {{ $salesman->date_of_birth ?? '--' }}
                                    </td>
                                    @hasPermission('shop.salesman.toggle')
                                    <td>
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.salesman.toggle', $salesman->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $salesman->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.salesman.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.salesman.edit',$salesman->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
                                            >
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
                    <div class="my-3">
                        {{ $salesmans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection