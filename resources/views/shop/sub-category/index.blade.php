@extends('layouts.app')
@section('header-title', __('Sub Categories'))

@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Sub Categories') }}
        </h4>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createSubCategory" class="btn py-2 btn-primary">
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
                                <th>{{ __('Status') }}</th>
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
                                    <img src="{{ $subCategory->thumbnail }}" width="50">
                                </td>
                                <td>
                                    @forelse ($subCategory->categories as $category)
                                        <span class="badge rounded-pill text-bg-primary me-1">{{ $category->name }}</span>
                                    @empty
                                        N/A
                                    @endforelse
                                </td>

                                <td>{{ $subCategory->name }}</td>

                                <td>
                                    @if($subCategory->shop_id && $subCategory->shop_id != $rootShop?->id && $subCategory->shop)
                                        <span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 12px;">
                                            {{ $subCategory->shop->name }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2 py-1" style="font-size: 12px;">
                                            {{ __('Super Admin') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0);">
                                            <input type="checkbox" {{ $subCategory->is_active ? 'checked' : '' }}>
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
            {{ $subCategories->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Create SubCategory Modal ===-->
    <form action="{{ route('shop.subcategory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="createSubCategory" tabindex="-1" aria-labelledby="createSubCategoryLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSubCategoryLabel">
                            {{ __('Create Sub Category') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_select" class="form-label">{{ __('Select Category') }} <span class="text-danger">*</span></label>
                            <select name="category[]" id="category_select" class="form-control select2" data-placeholder="{{ __('Select Category') }}" multiple required style="width: 100%;">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @if(isset($errors) && $errors->has('category'))
                                <p class="text text-danger m-0">{{ $errors->first('category') }}</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Sub Category Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{ __('Enter Sub Category Name') }}" value="{{ old('name') }}" required />
                            @if(isset($errors) && $errors->has('name'))
                                <p class="text text-danger m-0">{{ $errors->first('name') }}</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">{{ __('Sub Category Image') }}</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewSubCategoryImg(this)" />
                            @if(isset($errors) && $errors->has('thumbnail'))
                                <p class="text text-danger m-0">{{ $errors->first('thumbnail') }}</p>
                            @endif
                            <div class="mt-2 text-center d-none" id="previewSubCategoryImgContainer">
                                <img id="previewSubCategoryImgTag" src="#" alt="SubCategory Image Preview" class="img-thumbnail" style="max-height: 120px;" />
                            </div>
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

    <script>
        function previewSubCategoryImg(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewSubCategoryImgTag').src = e.target.result;
                    document.getElementById('previewSubCategoryImgContainer').classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                $('#category_select').select2({
                    placeholder: "{{ __('Select Category') }}",
                    allowClear: true,
                    dropdownParent: $('#createSubCategory')
                });
            }
        });

        $('#createSubCategory').on('shown.bs.modal', function () {
            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                $('#category_select').select2({
                    placeholder: "{{ __('Select Category') }}",
                    allowClear: true,
                    dropdownParent: $('#createSubCategory')
                });
            }
        });
    </script>

    @if(isset($errors) && ($errors->has('name') || $errors->has('category') || $errors->has('thumbnail')))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var createSubCategoryModal = new bootstrap.Modal(document.getElementById('createSubCategory'));
                createSubCategoryModal.show();
            });
        </script>
    @endif
@endsection
