@extends('layouts.app')
@section('header-title', __('Category List'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{ __('Category List') }}
        </h4>
        <div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#createCategory" class="btn py-2 btn-primary">
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
                        {{ __('Categories') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table border-left-right table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Thumbnail') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
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
                                    <img src="{{ $category->thumbnail }}" width="50">
                                </td>

                                <td>{{ $category->name }}</td>

                                <td>
                                    @if($category->shop_id && $category->shop_id != $rootShop?->id && $category->shop)
                                        <span class="badge rounded-pill text-bg-info px-2 py-1" style="font-size: 12px;">
                                            {{ $category->shop->name }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2 py-1" style="font-size: 12px;">
                                            {{ __('Super Admin') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <label class="switch mb-0">
                                        <a href="javascript:void(0)">
                                            <input type="checkbox" {{ $category->status ? 'checked' : '' }}>
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
            {{ $categories->withQueryString()->links() }}
        </div>

    </div>

    <!--=== Create Category Modal ===-->
    <form action="{{ route('shop.category.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="createCategory" tabindex="-1" aria-labelledby="createCategoryLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryLabel">
                            {{ __('Create Category') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Category Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="{{ __('Enter Category Name') }}" value="{{ old('name') }}" required />
                            @if(isset($errors) && $errors->has('name'))
                                <p class="text text-danger m-0">{{ $errors->first('name') }}</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="thumbnail" class="form-label">{{ __('Category Image') }} <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" required onchange="previewCategoryImg(this)" />
                            @if(isset($errors) && $errors->has('thumbnail'))
                                <p class="text text-danger m-0">{{ $errors->first('thumbnail') }}</p>
                            @endif
                            <div class="mt-2 text-center d-none" id="previewCategoryImgContainer">
                                <img id="previewCategoryImgTag" src="#" alt="Category Image Preview" class="img-thumbnail" style="max-height: 120px;" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="{{ __('Enter description') }}">{{ old('description') }}</textarea>
                            @if(isset($errors) && $errors->has('description'))
                                <p class="text text-danger m-0">{{ $errors->first('description') }}</p>
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

    <script>
        function previewCategoryImg(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewCategoryImgTag').src = e.target.result;
                    document.getElementById('previewCategoryImgContainer').classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    @if(isset($errors) && ($errors->has('name') || $errors->has('thumbnail') || $errors->has('description')))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var createCategoryModal = new bootstrap.Modal(document.getElementById('createCategory'));
                createCategoryModal.show();
            });
        </script>
    @endif
@endsection
