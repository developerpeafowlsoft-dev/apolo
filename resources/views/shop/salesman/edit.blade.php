@extends('layouts.app')
@section('header-title', __('Salesman'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Update Salesman')}}
        </h4>
    </div>
    <form id="formData" action="{{route('shop.salesman.update',$salesman->id)}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        {{-- Account Information Card 1 --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input label="First Name" name="name" type="text"
                                             placeholder="Enter Name" required="true" :value="$salesman->name" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input label="Last Name" name="last_name" type="text"
                                             placeholder="Enter Name" :value="$salesman->last_name" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            @php
                                $phone = $salesman->phone;
                                $phone = str_replace('(', '', $phone);
                                $phone = str_replace(')', '', $phone);
                                $phone = str_replace('-', '', $phone);
                                $phone = str_replace('+', '', $phone);
                            @endphp
                            <x-input label="Phone Number" name="phone" type="number"
                                     placeholder="Enter phone number" required="true" :value="$phone"/>
                        </div>
                        <div class="mt-3">
                            <x-input type="email" name="email" label="Email"
                                     placeholder="Enter Email Address" :value="$salesman->email" />
                        </div>

                        <div class="mt-3">
                            <x-select label="Gender" name="gender">
                                <option value="male" {{ $salesman->gender == 'male' ? 'selected' : '' }}>
                                    {{ __('Male') }}
                                </option>
                                <option value="female" {{ $salesman->gender == 'female' ? 'selected' : '' }}>
                                    {{ __('Female') }}
                                </option>
                                <option value="other" {{ $salesman->gender == 'other' ? 'selected' : '' }}>
                                    {{ __('Other') }}
                                </option>
                            </x-select>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="ratio1x1">
                                <img id="previewProfile"
                                     src="{{ $salesman->thumbnail ?? 'https://placehold.co/500x500/png' }}"
                                     alt="photo" width="100%">
                            </div>
                        </div>
                        <div class="mt-2">
                            <x-file name="src" label="User profile (Ratio 1:1)"
                                    preview="previewProfile" />
                        </div>

                        <div class="mt-3">
                            <x-input type="date" name="date_of_birth" label="Date of Birth"
                                     placeholder="Enter Date of Birth" :value="$salesman->date_of_birth" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-3 justify-content-end align-items-center my-3">
            <button type="reset" class="btn btn-outline-secondary rounded py-2">
                {{ __('Reset') }}
            </button>
            <button type="submit" class="btn btn-primary rounded py-2 px-5">
                {{ __('Update') }}
            </button>
        </div>
    </form>
@endsection