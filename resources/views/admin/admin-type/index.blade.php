@extends('layouts.app')

@section('title', __('Account Type'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center justify-content-between">
            <div>
                <i class="bi bi-bank2"></i> {{ __('Account Type') }}
            </div>
            @if(isset($account))
                <div>
                    <a href="{{ route('admin.accountType.index') }}" class="btn py-2 btn-primary">
                        <i class="bi bi-patch-plus"></i>
                        {{ __('Create New') }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <form id="formData" action="{{ isset($account) ? route('admin.accountType.update', $account->id) : route('admin.accountType.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($account))
            @method('PUT')
            <input type="hidden" name="id" value="{{ $account->id }}">
        @endif
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <x-input type="text" label="Account Type" name="name" placeholder="Enter Account Type" value="{{ old('name', $account->name ?? '') }}" required="true"/>
                    </div>
                </div>
            </div>
        </div>
        @hasPermission('admin.accountType.store')
        <div class="d-flex justify-content-end mt-4 mb-3">
            <button type="submit" class="btn btn-primary py-2.5 px-3">
                {{ isset($account) ?  __('Update') : __('Save') }}
            </button>
        </div>
        @endhasPermission
    </form>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table border-left-right table-responsive-md">
                            <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Account Type') }}</th>
                                @hasPermission('admin.accountType.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('admin.accountType.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($accountTypes as $key => $accountType)
                                @php
                                    $serial = $accountTypes->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $accountType->name ?? '' }}</td>
                                    @if($accountType->is_default)
                                        @hasPermission('admin.accountType.toggle')
                                        <td>
                                            <label class="switch mb-0">
                                                <a href="{{ route('admin.accountType.toggle', $accountType->id) }}" class="toggle-status-link">
                                                    <input type="checkbox" {{ $accountType->is_active ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </a>
                                            </label>
                                        </td>
                                        @endhasPermission
                                        @hasPermission('admin.accountType.edit')
                                        <td class="text-center">
                                            <div class="d-flex gap-3 justify-content-center">
                                                <a href="{{route('admin.accountType.edit',$accountType->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
                                                >
                                                    <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                                </a>
                                            </div>
                                        </td>
                                        @endhasPermission
                                    @else
                                        <td>{{__('-')}}</td>
                                        <td class="text-center">{{__('-')}}</td>
                                    @endif
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
                        {{ $accountTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css" />
@endpush
@push('scripts')
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>
    <script>
        function showCustomLoader(position = 'center') {
            const positions = {
                'center': 'translate(-50%, -50%)',
                'top': 'translate(-50%, 0)',
                'bottom': 'translate(-50%, -100%)',
                'left': 'translate(0, -50%)',
                'right': 'translate(-100%, -50%)',
            };

            HoldOn.open({
                theme: "custom",
                message: 'Please wait...',
                content: `
                <div style="
                    position: absolute;
                    top: ${position === 'center' ? '50%' : position === 'top' ? '10%' : position === 'bottom' ? '90%' : '50%'};
                    left: ${position === 'center' ? '50%' : position === 'left' ? '10%' : position === 'right' ? '90%' : '50%'};
                    transform: ${positions[position]};
                    z-index: 9999;
                ">
                    <img src="{{ asset('assets/images/APOLO_GIF.gif') }}" alt="Loading..." style="width:80px; height:auto;" />
                </div>
            `,
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            ['.toggle-status-link', '.editData'].forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    el.addEventListener('click', () => showCustomLoader('center'));
                });
            });

            const form = document.getElementById('formData');
            if (form) {
                form.addEventListener('submit', () => showCustomLoader('center'));
            }
        });
    </script>
@endpush