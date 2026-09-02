@extends('layouts.app')

@section('header-title', __('Edit Product'))

@section('content')
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-dark m-0">{{ __('Edit Product') }}</h4>
                @if(!empty($product->code))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded font-monospace" style="font-size: 11px;">
                        SKU: {{ $product->code }}
                    </span>
                @endif
                @if(!empty($product->designMaster?->design_number))
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded font-monospace" style="font-size: 11px;">
                        Design: {{ $product->designMaster->design_number }}
                    </span>
                @endif
            </div>
            <p class="text-muted mb-0 small mt-1">{{ __('Easily view and update product information, categories, inward variants, photos, and SEO.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop.product.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 rounded-2">
                <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Products') }}
            </a>
        </div>
    </div>

    <form action="{{ route('shop.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="editProductForm">
        @csrf
        @method('PUT')

        <!-- 2-Column Compact Dashboard Grid Layout -->
        <div class="row g-3">
            
            <!-- LEFT COLUMN (8 cols): Primary Info, Price Variants, Delivery & SEO -->
            <div class="col-lg-8">
                
                <!-- 1. Basic Product Information -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('1. Basic Information') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <x-input label="Product Name" name="name" type="text" placeholder="Product Name" required="true"
                                value="{{ $product->name }}" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                {{ __('Short Description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="short_description" class="form-control rounded-2" rows="2" placeholder="{{ __('Short Description') }}">{{ old('short_description') ?? $product->short_description }}</textarea>
                            @error('short_description')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold text-dark small">
                                {{ __('Description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="border rounded-2 overflow-hidden bg-white">
                                <div id="editor" style="min-height: 140px; max-height: 350px; overflow-y: auto">
                                    {!! old('description') ?? $product->description !!}
                                </div>
                            </div>
                            <input type="hidden" id="description" name="description"
                                value="{{ old('description') ?? $product->description }}">
                            @error('description')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Price Information & Inward Product Variants Table -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-currency-rupee text-primary fs-6"></i>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('2. Price & Inward Variant Stock') }}</h6>
                        </div>
                        @if($inwardProductData->isNotEmpty())
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                {{ $inwardProductData->count() }} {{ $inwardProductData->count() == 1 ? __('Variant') : __('Variants') }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-3">

                        <!-- Hidden fields kept for backend structure -->
                        <div class="row g-2 d-none">
                            <div class="col-6 col-md-3">
                                <x-input type="text" name="buy_price" label="Buying Price" placeholder="Buying Price"
                                    required="true" onlyNumber="true" :value="$product->buy_price" />
                            </div>

                            <div class="col-6 col-md-3">
                                <x-input type="text" name="price" label="Selling Price" placeholder="Selling Price"
                                    required="true" onlyNumber="true" :value="$product->price" />
                            </div>

                            <div class="col-6 col-md-3">
                                <x-input type="text" name="discount_price" label="Discount Price"
                                    placeholder="Discount Price" onlyNumber="true" :value="$product->discount_price" />
                            </div>

                            <div class="col-6 col-md-3">
                                <x-input type="text" name="quantity" label="Current Stock Quantity"
                                    placeholder="Current Stock Quantity" onlyNumber="true" :value="$product->quantity" />
                            </div>

                            <div class="col-6 col-md-3">
                                <x-input type="text" onlyNumber="true" name="min_order_quantity"
                                    label="Minimum Order Quantity" placeholder="Minimum Order Quantity"
                                    :value="$product->min_order_quantity" />
                            </div>
                        </div>

                        <!-- Inward Product Variants Table -->
                        @if($inwardProductData->isNotEmpty())
                            <div class="border rounded-3 overflow-hidden" id="inwardTableBox">
                                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                        <thead class="table-light border-bottom sticky-top" style="z-index: 1;">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">{{ __('SL') }}</th>
                                                <th style="min-width: 90px;">{{ __('Design No') }}</th>
                                                <th style="min-width: 90px;">{{ __('Color') }}</th>
                                                <th style="min-width: 70px;">{{ __('Size') }}</th>
                                                <th class="text-center" style="min-width: 60px;">{{ __('Qty') }}</th>
                                                <th class="text-end" style="min-width: 90px;">{{ __('Purc Rate') }}</th>
                                                <th class="text-end" style="min-width: 100px;">{{ __('Amount') }}</th>
                                                <th class="text-center" style="min-width: 70px;">{{ __('Disc (%)') }}</th>
                                                <th class="text-end" style="min-width: 90px;">{{ __('MRP') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalQty = 0;
                                                $totalAmount = 0;
                                                $totalMrp = 0;
                                            @endphp
                                            @foreach($inwardProductData as $index => $item)
                                                @php
                                                    $totalQty += $item->qty;
                                                    $totalAmount += $item->amount;
                                                    $totalMrp += $item->mrp;
                                                @endphp
                                                <tr>
                                                    <td class="text-center text-secondary fw-semibold">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-1.5 py-0.5" style="font-size: 11px;">{{ $item->design_no }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-1.5 py-0.5" style="font-size: 11px;">{{ $item->colors }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size: 11px;">{{ $item->sizes }}</span>
                                                    </td>
                                                    <td class="text-center font-monospace fw-bold text-dark">{{ $item->qty }}</td>
                                                    <td class="text-end font-monospace text-primary fw-medium">₹{{ number_format($item->purc_rate, 2) }}</td>
                                                    <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format($item->amount, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5" style="font-size: 11px;">{{ $item->disc_percent }}%</span>
                                                    </td>
                                                    <td class="text-end font-monospace text-success fw-bold">₹{{ number_format($item->mrp, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light border-top fw-bold sticky-bottom" style="font-size: 12.5px; z-index: 1;">
                                            <tr>
                                                <td colspan="4" class="text-end text-muted">{{ __('Total:') }}</td>
                                                <td class="text-center font-monospace text-dark fs-6">{{ $totalQty }}</td>
                                                <td class="text-end text-muted">-</td>
                                                <td class="text-end font-monospace text-dark fs-6">₹{{ number_format($totalAmount, 2) }}</td>
                                                <td></td>
                                                <td class="text-end font-monospace text-success fs-6">₹{{ number_format($totalMrp, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-info-circle me-1"></i> {{ __('No inward purchase variants linked to this product.') }}
                            </div>
                        @endif

                    </div>
                </div>

                @php
                    $hsnCode = $product->hsnMaster?->hsn_code ?? $product->hsn ?? $product->hsn_code ?? null;
                    if (!$hsnCode && isset($inwardProductData) && $inwardProductData->isNotEmpty()) {
                        $hsnCode = $inwardProductData->first()?->hsnMaster?->hsn_code ?? null;
                    }
                @endphp
                <!-- 3. Delivery & Dimensions -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-truck text-primary fs-6"></i>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('3. Delivery Dimensions') }}</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-2 px-2.5 py-1 text-decoration-none d-inline-flex align-items-center gap-1 font-size-12 fw-semibold" id="btn-estimate-dimensions" onclick="estimateDimensions()">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ __('Estimate') }}</span>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-sm-6 col-md-3">
                                <x-input type="text" name="length" label="Length (cm)" placeholder="Length"
                                         required="true" onlyNumber="true" :value="$product->length" />
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <x-input type="text" name="width" label="Width (cm)" placeholder="Width"
                                         required="true" onlyNumber="true" :value="$product->width" />
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <x-input type="text" name="height" label="Height (cm)" placeholder="Height"
                                         required="true" onlyNumber="true" :value="$product->height"/>
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <x-input type="text" name="weight" label="Weight (kg)" placeholder="Weight"
                                         required="true" onlyNumber="true" :value="$product->weight" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. SEO Information -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-search text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('4. SEO & Search Engine Optimization') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Meta Title') }}
                            </label>
                            <input type="text" name="meta_title" id="meta_title" placeholder="{{ __('Meta Title') }}"
                                class="form-control rounded-2" value="{{ old('meta_title', $product->meta_title) }}" />
                            @error('meta_title')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Meta Description') }}
                            </label>
                            <textarea name="meta_description" id="meta_description" placeholder="{{ __('Meta Description') }}" class="form-control rounded-2" rows="2">{{ old('meta_description', $product->meta_description) }}</textarea>
                            @error('meta_description')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label for="tags" class="form-label fw-semibold text-dark small mb-1">@lang('Meta Keywords')</label>
                            <select id="tags" name="meta_keywords[]" class="form-control selectTags w-100" multiple style="width: 100%;">
                                @foreach (old('meta_keywords', $metaKeywords) as $keyword)
                                    <option value="{{ $keyword }}" selected>{{ $keyword }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>@lang('Write keywords and Press enter to add new one')</small>
                            @error('meta_keywords')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (4 cols): Categorization, Media Photos & Video -->
            <div class="col-lg-4">
                
                <!-- 5. Categorization & Product Code -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('5. Categorization & SKU') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        
                        <!-- SKU Code Input -->
                        <div class="mb-3 p-2.5 bg-light-subtle border rounded-3">
                            <label class="form-label fw-semibold text-dark small d-flex align-items-center justify-content-between mb-1">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span>
                                        {{ __('Product SKU / Code') }}
                                        <span class="text-danger">*</span>
                                    </span>
                                    <span class="text-muted cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="{{ __('Unique product barcode SKU code') }}">
                                        <i class="bi bi-info-circle text-primary"></i>
                                    </span>
                                </div>
                                <span class="text-primary fw-semibold cursor-pointer small text-decoration-none" onclick="generateCode()" role="button">
                                    <i class="bi bi-magic me-0.5"></i> {{ __('Generate') }}
                                </span>
                            </label>
                            <input type="text" id="barcode" name="code" placeholder="Ex: 134543"
                                class="form-control rounded-2 font-monospace fw-bold text-primary" value="{{ $product->code }}"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" />
                            @error('code')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                {{ __('Select Category') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="category" class="form-select select2 w-100">
                                <option value="" disabled>
                                    {{ __('Select Category') }}
                                </option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ in_array($category->id, $product->categories?->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sub Category -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                {{ __('Select Sub Categories') }}
                            </label>
                            <select name="sub_category[]" class="form-select select2 w-100" multiple
                                data-placeholder="{{ __('Select Sub Category') }}">
                                @foreach ($subCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}"
                                        {{ in_array($subCategory->id, old('sub_category', $selectedSubCategoryIds ?? $product->subcategories?->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                        {{ $subCategory->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sub_category')
                                <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Brand & Unit side by side -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <x-select label="Select Brand" name="brand">
                                    <option value="">
                                        {{ __('Select Brand') }}
                                    </option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ $brand->id == $product->brand_id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-6">
                                <x-select label="Select Unit" name="unit" placeholder="Select Unit">
                                    <option value="">
                                        {{ __('Select Unit') }}
                                    </option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $unit->id == $product->unit_id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 6. Photos & Thumbnails -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-images text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('6. Photos & Gallery') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        
                        <!-- Main Thumbnail Box -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small d-block mb-1">
                                {{ __('Main Thumbnail') }} <span class="text-muted fw-normal font-monospace" style="font-size: 11px;">(1:1 / 500x500 px)</span>
                                <span class="text-danger">*</span>
                            </label>
                            @error('thumbnail')
                                <p class="text-danger small mt-1 mb-1">{{ $message }}</p>
                            @enderror

                            <label for="thumbnail" class="additionThumbnail cursor-pointer d-block border rounded-3 p-2 bg-light-subtle text-center overflow-hidden shadow-2xs position-relative" style="height: 140px;">
                                <img src="{{ $product->thumbnail ?? asset('default/upload.png') }}" id="preview"
                                    alt="thumbnail preview" class="w-100 h-100 object-fit-contain rounded-2">
                            </label>
                            <input id="thumbnail" accept="image/*" type="file" name="thumbnail" class="d-none"
                                onchange="previewFile(event, 'preview')">
                            <span class="text-muted small d-block text-center mt-1"><i class="bi bi-cloud-arrow-up me-1"></i> {{ __('Click image to upload') }}</span>
                        </div>

                        <!-- Gallery Images Box -->
                        <div>
                            <label class="form-label fw-bold text-dark small d-block mb-1">
                                {{ __('Gallery Images') }} <span class="text-muted fw-normal font-monospace" style="font-size: 11px;">(1:1)</span>
                            </label>
                            @error('additionThumbnail')
                                <p class="text-danger small mt-1 mb-1">{{ $message }}</p>
                            @enderror

                            <div class="d-flex flex-wrap gap-2 mt-1" id="additionalElements">
                                <!-- Previous additional thumbnails -->
                                @foreach ($product->medias as $media)
                                    @php
                                        $source = asset('default/upload.png');
                                        if (Storage::disk('public')->exists($media->src)) {
                                            $source = Storage::disk('public')->url($media->src);
                                        }
                                    @endphp

                                    <div id="additionShow" class="position-relative">
                                        <label for="previousThumbnailShow{{ $media->id }}"
                                            class="additionThumbnail cursor-pointer border rounded-3 p-1 bg-white shadow-2xs d-block" style="width: 75px; height: 75px;">
                                            <img src="{{ $source }}" id="previewShow{{ $media->id }}"
                                                alt="thumbnail" class="w-100 h-100 object-fit-cover rounded-2">
                                            <a href="{{ route('shop.product.remove.thumbnail', ['product' => $product->id, 'media' => $media->id]) }}"
                                                class="delete btn btn-danger btn-sm rounded-circle p-0 position-absolute top-0 end-0 translate-middle shadow-sm" style="width: 20px; height: 20px; line-height: 18px;">
                                                <i class="bi bi-x-lg small" style="font-size: 10px;"></i>
                                            </a>
                                        </label>
                                        <input type="hidden" name="previousThumbnail[{{ $loop->index }}][id]"
                                            value="{{ $media->id }}">
                                        <input id="previousThumbnailShow{{ $media->id }}" accept="image/*"
                                            type="file" name="previousThumbnail[{{ $loop->index }}][file]"
                                            class="d-none"
                                            onchange="previewFile(event, 'previousThumbnailShow{{ $media->id }}')" />
                                    </div>
                                @endforeach

                                <!-- New additional thumbnail placeholder -->
                                <div id="addition">
                                    <label for="additionThumbnail1" class="additionThumbnail cursor-pointer border border-dashed rounded-3 p-1 bg-white shadow-2xs d-flex align-items-center justify-content-center position-relative" style="width: 75px; height: 75px;">
                                        <img src="{{ asset('default/upload.png') }}" id="preview2" alt="upload image"
                                            class="w-100 h-100 object-fit-contain rounded-2">
                                        <button onclick="removeThumbnail('addition')" id="removeThumbnail1"
                                            type="button" class="delete btn btn-danger btn-sm rounded-circle p-0 position-absolute top-0 end-0 translate-middle shadow-sm"
                                            style="display: none; width: 20px; height: 20px; line-height: 18px;">
                                            <i class="bi bi-x-lg small" style="font-size: 10px;"></i>
                                        </button>
                                    </label>
                                    <input id="additionThumbnail1" accept="image/*" type="file"
                                        name="additionThumbnail[]" class="d-none"
                                        onchange="previewAdditionalFile(event, 'preview2', 'removeThumbnail1')">
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- 7. Product Video Section -->
                <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                    <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-film text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('7. Product Video') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-2">
                            <label for="uploadType" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Select Video Type') }}
                            </label>
                            <select class="form-select rounded-2" name="uploadVideo[type]" id="uploadType"
                                onchange="toggleFields()">
                                <option value="file" {{ $product->video?->type == 'file' ? 'selected' : '' }}>
                                    {{ __('Upload Video File') }}
                                </option>
                                <option value="youtube" {{ $product->video?->type == 'youtube' ? 'selected' : '' }}>
                                    {{ __('YouTube Link') }}
                                </option>
                                <option value="vimeo" {{ $product->video?->type == 'vimeo' ? 'selected' : '' }}>
                                    {{ __('Vimeo Link') }}
                                </option>
                                <option value="dailymotion"
                                    {{ $product->video?->type == 'dailymotion' ? 'selected' : '' }}>
                                    {{ __('Dailymotion Link') }}
                                </option>
                            </select>
                        </div>

                        <!-- Upload File Section -->
                        <div class="mb-1" id="fileUploadField">
                            <label for="productVideo" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Upload Product Video') }}
                            </label>
                            <input type="file" class="form-control rounded-2" name="uploadVideo[file]" id="productVideo"
                                accept="video/*">
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>{{ __('MP4, AVI, MOV, WMV') }}
                            </small>
                        </div>

                        <!-- YouTube Link Section -->
                        <div class="mb-1 d-none" id="youtubeField">
                            <label for="youtubeLink" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('YouTube Video Link') }}
                            </label>
                            <textarea class="form-control rounded-2" name="uploadVideo[youtube_url]" id="youtubeLink" rows="2"
                                placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/..." title="YouTube video player"...'>{{ $product->video?->type == 'youtube' ? $product->video->url : '' }}</textarea>
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>{{ __('Paste YouTube embed code') }}</small>
                        </div>

                        <!-- Vimeo Link Section -->
                        <div class="mb-1 d-none" id="vimeoField">
                            <label for="vimeoLink" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Vimeo Video Link') }}
                            </label>
                            <textarea name="uploadVideo[vimeo_url]" id="vimeoLink" class="form-control rounded-2" rows="2">{{ $product->video?->type == 'vimeo' ? $product->video->url : '' }}</textarea>
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>{{ __('Paste Vimeo embed code') }}</small>
                        </div>

                        <!-- Dailymotion Link Section -->
                        <div class="mb-1 d-none" id="dailymotionField">
                            <label for="dailymotionLink" class="form-label fw-semibold text-dark small mb-1">
                                {{ __('Dailymotion Video Link') }}
                            </label>
                            <textarea name="uploadVideo[dailymotion_url]" id="dailymotionLink" class="form-control rounded-2" rows="2">{{ $product->video?->type == 'dailymotion' ? $product->video->url : '' }}</textarea>
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>{{ __('Paste Dailymotion embed code') }}</small>
                        </div>

                        @error('uploadVideo.file')
                            <p class="text-danger small mt-1 mb-0">{{ $message }}</p>
                        @enderror

                        <!-- Duplicate Video Embed Error Message -->
                        <p class="text-danger small mt-1 mb-0 d-none fw-semibold" id="videoEmbedError">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ __('Only one video embed code is allowed. Please remove the existing video before adding another one.') }}
                        </p>

                        <!-- Real-time Video Preview Container -->
                        <div class="mt-3 p-2.5 bg-light-subtle border rounded-3 text-center d-none" id="videoPreviewBox">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-dark small d-flex align-items-center gap-1.5 m-0">
                                    <i class="bi bi-play-circle-fill text-primary"></i> {{ __('Video Preview') }}
                                </label>
                                <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0.5 rounded-2 small" onclick="removeProductVideo()" style="font-size: 11px;">
                                    <i class="bi bi-trash me-1"></i> {{ __('Remove Video') }}
                                </button>
                            </div>
                            <div id="videoPreviewContent" class="overflow-hidden rounded-2 bg-dark"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sticky Footer Form Actions Bar -->
        <div class="sticky-bottom bg-white border-top shadow-lg p-3 rounded-top-3 mt-3 d-flex align-items-center justify-content-between" style="z-index: 1020;">
            <span class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> {{ __('Make sure to save changes after editing.') }}
            </span>
            <div class="d-flex gap-2">
                <button type="reset" class="btn btn-outline-secondary fw-semibold px-3 rounded-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reset') }}
                </button>
                <button type="submit" class="btn btn-primary fw-semibold px-4 rounded-2">
                    <i class="bi bi-check2-circle me-1.5"></i> {{ __('Update Product') }}
                </button>
            </div>
        </div>

    </form>
@endsection

@push('css')
    <style>
        .box-title {
            background: #f1f5f9;
            padding: 6px 10px;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
        }

        .app-theme-dark .box-title {
            background: #2d2d2d;
            border-color: #2d2d2d;
        }

        #colorBox,
        #sizeBox {
            margin-top: 20px;
        }

        .boxName {
            font-size: 16px;
            margin-bottom: 0;
        }

        .extraPriceForm {
            padding: 4px 6px;
            min-height: 34px;
        }

        #selectedSizesTableBody tr:last-child td,
        #selectedColorsTableBody tr:last-child td {
            border: 0 !important;
        }
        
        .additionThumbnail {
            transition: all 0.2s ease-in-out;
        }
        .additionThumbnail:hover {
            border-color: #0d6efd !important;
        }

        /* Select2 Keywords input fix */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            min-height: 38px !important;
            padding: 2px 6px !important;
            background-color: #fff !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let videoCleared = false;
        let duplicatePasteBlocked = false;

        function removeProductVideo() {
            videoCleared = true;
            duplicatePasteBlocked = false;
            $('#productVideo').val('');
            $('#youtubeLink').val('').removeClass('is-invalid');
            $('#vimeoLink').val('').removeClass('is-invalid');
            $('#dailymotionLink').val('').removeClass('is-invalid');
            $('#videoEmbedError').addClass('d-none');
            $('#videoPreviewContent').empty();
            $('#videoPreviewBox').addClass('d-none');
        }

        function checkDuplicateVideoEmbed(inputVal, targetElem) {
            const errorElem = $('#videoEmbedError');
            const iframeMatches = (inputVal.match(/<iframe/gi) || []).length;

            if (duplicatePasteBlocked || iframeMatches > 1) {
                $(targetElem).addClass('is-invalid');
                errorElem.text("{{ __('Only one video embed code is allowed. Please remove the existing video before adding another one.') }}").removeClass('d-none');
                return true; // Has duplicate error
            } else {
                $(targetElem).removeClass('is-invalid');
                errorElem.addClass('d-none');
                return false; // Valid
            }
        }

        function updateVideoPreview() {
            const selectedType = $('#uploadType').val();
            const previewBox = $('#videoPreviewBox');
            const previewContent = $('#videoPreviewContent');

            if (selectedType === 'file') {
                previewContent.empty();
                const fileInput = document.getElementById('productVideo');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    const fileUrl = URL.createObjectURL(fileInput.files[0]);
                    previewContent.html(`<video controls class="w-100 rounded-2" style="max-height: 220px;" src="${fileUrl}"></video>`);
                    previewBox.removeClass('d-none');
                    return;
                }
                if (!videoCleared) {
                    @if($product->video?->type == 'file' && !empty($product->video?->url))
                        const existingUrl = "{{ asset($product->video->url) }}";
                        previewContent.html(`<video controls class="w-100 rounded-2" style="max-height: 220px;" src="${existingUrl}"></video>`);
                        previewBox.removeClass('d-none');
                        return;
                    @endif
                }
            } else if (selectedType === 'youtube') {
                const linkElem = $('#youtubeLink');
                let inputVal = linkElem.val().trim();
                checkDuplicateVideoEmbed(inputVal, linkElem);

                if (inputVal) {
                    let embedUrl = '';
                    let srcMatch = inputVal.match(/src=["']([^"']+)["']/i);
                    if (srcMatch && srcMatch[1]) {
                        embedUrl = srcMatch[1];
                    } else if (inputVal.includes('v=')) {
                        let videoId = inputVal.split('v=')[1].split('&')[0];
                        embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    } else if (inputVal.includes('youtu.be/')) {
                        let videoId = inputVal.split('youtu.be/')[1].split('?')[0];
                        embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    } else if (inputVal.includes('embed/')) {
                        let videoId = inputVal.split('embed/')[1].split('?')[0];
                        embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    } else {
                        embedUrl = inputVal;
                    }

                    if (embedUrl) {
                        previewContent.html(`<iframe width="100%" height="220" src="${embedUrl}" frameborder="0" allowfullscreen class="rounded-2"></iframe>`);
                        previewBox.removeClass('d-none');
                        return;
                    }
                }
            } else if (selectedType === 'vimeo') {
                const linkElem = $('#vimeoLink');
                let inputVal = linkElem.val().trim();
                checkDuplicateVideoEmbed(inputVal, linkElem);

                if (inputVal) {
                    let embedUrl = '';
                    let srcMatch = inputVal.match(/src=["']([^"']+)["']/i);
                    if (srcMatch && srcMatch[1]) {
                        embedUrl = srcMatch[1];
                    } else {
                        let videoId = inputVal.replace(/[^0-9]/g, '');
                        embedUrl = videoId ? `https://player.vimeo.com/video/${videoId}` : inputVal;
                    }

                    if (embedUrl) {
                        previewContent.html(`<iframe width="100%" height="220" src="${embedUrl}" frameborder="0" allowfullscreen class="rounded-2"></iframe>`);
                        previewBox.removeClass('d-none');
                        return;
                    }
                }
            } else if (selectedType === 'dailymotion') {
                const linkElem = $('#dailymotionLink');
                let inputVal = linkElem.val().trim();
                checkDuplicateVideoEmbed(inputVal, linkElem);

                if (inputVal) {
                    let embedUrl = '';
                    let srcMatch = inputVal.match(/src=["']([^"']+)["']/i);
                    if (srcMatch && srcMatch[1]) {
                        embedUrl = srcMatch[1];
                    } else {
                        embedUrl = inputVal;
                    }

                    if (embedUrl) {
                        previewContent.html(`<iframe width="100%" height="220" src="${embedUrl}" frameborder="0" allowfullscreen class="rounded-2"></iframe>`);
                        previewBox.removeClass('d-none');
                        return;
                    }
                }
            }

            previewContent.empty();
            previewBox.addClass('d-none');
        }

        $(document).on('paste', '#youtubeLink, #vimeoLink, #dailymotionLink', function(e) {
            const currentVal = $(this).val();
            const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            const currentIframeMatches = (currentVal.match(/<iframe/gi) || []).length;
            const pastedIframeMatches = (pastedText.match(/<iframe/gi) || []).length;

            if ((currentIframeMatches > 0 && pastedIframeMatches > 0) || (currentIframeMatches + pastedIframeMatches > 1)) {
                e.preventDefault();
                duplicatePasteBlocked = true;
                $(this).addClass('is-invalid');
                $('#videoEmbedError')
                    .text("{{ __('Only one video embed code is allowed. Please remove the existing video before adding another one.') }}")
                    .removeClass('d-none');
                return false;
            }
        });

        $(document).on('keyup input', '#youtubeLink, #vimeoLink, #dailymotionLink', function() {
            const currentVal = $(this).val();
            const iframeMatches = (currentVal.match(/<iframe/gi) || []).length;
            if (iframeMatches <= 1 && $(this).val().trim() === '') {
                duplicatePasteBlocked = false;
            }
        });

        function toggleFields(isUserSwitch = true) {
            if (isUserSwitch) {
                removeProductVideo();
            }

            // Hide all fields
            document.getElementById('fileUploadField').classList.add('d-none');
            document.getElementById('youtubeField').classList.add('d-none');
            document.getElementById('vimeoField').classList.add('d-none');
            document.getElementById('dailymotionField').classList.add('d-none');

            // Get selected type
            const selectedType = document.getElementById('uploadType').value;

            // Show relevant field
            if (selectedType === 'file') {
                document.getElementById('fileUploadField').classList.remove('d-none');
            } else if (selectedType === 'youtube') {
                document.getElementById('youtubeField').classList.remove('d-none');
            } else if (selectedType === 'vimeo') {
                document.getElementById('vimeoField').classList.remove('d-none');
            } else if (selectedType === 'dailymotion') {
                document.getElementById('dailymotionField').classList.remove('d-none');
            }

            updateVideoPreview();
        }

        $(document).on('change input paste keyup', '#productVideo, #youtubeLink, #vimeoLink, #dailymotionLink', function() {
            setTimeout(updateVideoPreview, 100);
        });

        $(document).ready(function() {
            toggleFields(false);

            var productName = $('input[name="name"]').val();

            var replace = productName.replace(/&amp;/g, '&').replace(/&#039;/g, "'").replace(/&quot;/g, '"')
                .replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            $('input[name="name"]').val(replace);

            $('.sizeSelector').select2();

            $(".selectTags").select2({
                tags: true,
                placeholder: "{{ __('Write keywords and Press enter to add new one') }}"
            });

            $('#price').on('input', function() {
                var productPrice = $(this).val() ?? 0;
                var productDiscountPrice = $('#discount_price').val() ?? 0;
                var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;
                $('.mainProductPrice').text(mainPrice);
            });

            $('#discount_price').on('input', function() {
                var productPrice = $('#price').val() ?? 0;
                var productDiscountPrice = $(this).val() ?? 0;
                var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;
                $('.mainProductPrice').text(mainPrice);
            });

            $('.sizeSelector').on('change', function() {

                var productPrice = $('#price').val() ?? 0;
                var productDiscountPrice = $('#discount_price').val() ?? 0;
                var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;

                // Get the selected options
                var selectedOptions = $(this).find(':selected');

                // Check if there are selected options
                if (selectedOptions.length > 0) {
                    $('#sizeBox').show();
                } else {
                    $('#sizeBox').hide();
                }

                selectedOptions.each(function() {
                    var sizeName = $(this).data('size');
                    var sizeId = $(this).val();

                    // Check if the row already exists
                    if (!$(`#selectedSizeRow_${sizeId}`).length) {
                        $('#selectedSizesTableBody').append(`
                            <tr id="selectedSizeRow_${sizeId}" style="display: table-row !important">
                                <td>
                                    <h4 class="mb-0 boxName">${sizeName}</h4>
                                    <input type="hidden" name="size[${sizeId}][name]" value="${sizeName}">
                                    <input type="hidden" name="size[${sizeId}][id]" value="${sizeId}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                                        <span class="bg-light px-2 py-1 rounded">
                                            <i class="fa-solid fa-plus"></i>
                                        </span>
                                        <input type="text" class="form-control" name="size[${sizeId}][price]" value="0" style="width: 140px" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{0,2}|\d*)$/, '$1');" />
                                    </div>
                                </td>
                                <td>
                                    <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                                        onclick="deleteSizeRow(${sizeId})">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    }
                });

                $(this).find(':not(:selected)').each(function() {
                    var sizeId = $(this).val();
                    $(`#selectedSizeRow_${sizeId}`).remove();
                });

                setDefaultPrice();
            });

            // Add color wise extra price
            $('.colorSelect').on('change', function() {

                var selectedOptions = $(this).find(':selected');

                if (selectedOptions.length > 0) {
                    $('#colorBox').show();
                } else {
                    $('#colorBox').hide();
                }

                var productPrice = $('#price').val() ?? 0;
                var productDiscountPrice = $('#discount_price').val() ?? 0;
                var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;

                selectedOptions.each(function() {
                    var colorName = $(this).data('name');
                    var colorCode = $(this).data('color');
                    var colorId = $(this).val();

                    // Check if the row already exists
                    if (!$(`#selectedColorRow_${colorId}`).length) {
                        $('#selectedColorsTableBody').append(`
                            <tr id="selectedColorRow_${colorId}" style="display: table-row !important">
                                <td>
                                    <h4 class="mb-0 boxName d-flex align-items-center gap-1">
                                        <span style="background-color:${colorCode};width:20px;height:19px;display:inline-block; border-radius:5px;"></span>
                                        ${colorName}
                                    </h4>
                                    <input type="hidden" name="color[${colorId}][name]" value="${colorName}">
                                    <input type="hidden" name="color[${colorId}][id]" value="${colorId}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                                        <span class="bg-light px-2 py-1 rounded">
                                            <i class="fa-solid fa-plus"></i>
                                        </span>
                                        <input type="text" class="form-control extraPriceForm" name="color[${colorId}][price]" value="0" style="width: 140px" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{0,2}|\d*)$/, '$1');">
                                    </div>
                                </td>
                                <td>
                                    <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                                        onclick="deleteColorRow(${colorId})">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    }
                });

                // Remove the row from the table
                $(this).find(':not(:selected)').each(function() {
                    var colorId = $(this).val();
                    $(`#selectedColorRow_${colorId}`).remove();
                });

                setDefaultPrice();
            });

            // Get the category ID
            $('select[name="category"]').on('change', function() {
                var categoryId = $(this).val();

                if (categoryId) {
                    $.ajax({
                        url: '/api/sub-categories?category_id=' + categoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            var subCategorySelected = $('select[name="sub_category[]"]');
                            subCategorySelected.empty();

                            $.each(data.data.sub_categories, function(key, value) {
                                subCategorySelected.append('<option value="' + value
                                    .id +
                                    '">' + value.name + '</option>');
                            });
                            subCategorySelected.trigger('change');
                        },
                        error: function() {
                            console.log('Error retrieving subcategories. Please try again.');
                        }
                    });
                } else {
                    $('select[name="subCategory[]"]').empty();
                }
            });

            // form submit loader
            $('form').on('submit', function() {
                var submitButton = $(this).find('button[type="submit"]');

                submitButton.prop('disabled', true);
                submitButton.removeClass('px-5');

                submitButton.html(`<div class="d-flex align-items-center gap-1">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span>Updating...</span>
                </div>`)
            });
        });

        // remove size from price section
        function deleteSizeRow(id) {
            $(`#selectedSizeRow_${id}`).remove();

            $('.sizeSelector option').each(function() {
                if ($(this).val() == id) {
                    $(this).prop('selected', false);
                }
            });
            $('.sizeSelector').trigger('change');

            setDefaultPrice();
        }

        // remove color from price section
        function deleteColorRow(id) {
            $(`#selectedColorRow_${id}`).remove();

            $('.colorSelect option').each(function() {
                if ($(this).val() == id) {
                    $(this).prop('selected', false);
                }
            });
            $('.colorSelect').trigger('change');

            setDefaultPrice();
        }

        // set default price
        function setDefaultPrice() {
            $('#selectedColorsTableBody').find('tr').each(function() {
                let index = $(this).index();
                var rowId = $(this).attr('id').split('_')[1];

                if (index == 0) {
                    var priceInput = $(`input[name="color[${rowId}][price]"]`);
                    priceInput.val(0);
                    priceInput.attr('type', 'hidden');

                    $('#defaultPriceColor').remove();
                    $(`<span id="defaultPriceColor" class="defaultPrice fst-italic">Default Price</span>`)
                        .insertAfter(priceInput);
                }
            });

            $('#selectedSizesTableBody').find('tr').each(function() {
                let index = $(this).index();
                var rowId = $(this).attr('id').split('_')[1];

                if (index == 0) {
                    var priceInput = $(`input[name="size[${rowId}][price]"]`);
                    priceInput.val(0);
                    priceInput.attr('type', 'hidden');

                    $('#defaultPriceSize').remove();
                    $(`<span id="defaultPriceSize" class="defaultPrice fst-italic">Default Price</span>`)
                        .insertAfter(priceInput);
                }
            });
        }

        // initially set default price
        setDefaultPrice();
    </script>

    <!-- additional thumbnail script -->
    <script>
        var thumbnailCount = 1;

        const previewAdditionalFile = (event, id, removeId) => {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById(id);
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);

            // increment count
            thumbnailCount++;

            document.getElementById(removeId).style.display = 'block';

            // Create a new box dynamically
            const newThumbnailId = `additionThumbnail${thumbnailCount + 1}`;
            const newPreviewId = `preview${thumbnailCount + 1}`;
            const mainId = 'addition' + thumbnailCount + 1;

            // Add the new box
            const newThumbnailBox = document.createElement('div');
            newThumbnailBox.id = mainId;

            newThumbnailBox.innerHTML = `
            <label for="${newThumbnailId}" class="additionThumbnail cursor-pointer border border-dashed rounded-3 p-1 bg-white shadow-2xs d-flex align-items-center justify-content-center position-relative" style="width: 75px; height: 75px;">
                <img src="{{ asset('default/upload.png') }}" id="${newPreviewId}" alt="" class="w-100 h-100 object-fit-contain rounded-2">
                <button onclick="removeThumbnail('${mainId}')" type="button" id="removeThumbnail${thumbnailCount + 1}" class="delete btn btn-danger btn-sm rounded-circle p-0 position-absolute top-0 end-0 translate-middle shadow-sm" style="display: none; width: 20px; height: 20px; line-height: 18px;"><i class="bi bi-x-lg small" style="font-size: 10px;"></i></button>
                <input id="${newThumbnailId}" accept="image/*" type="file" name="additionThumbnail[]" class="d-none" onchange="previewAdditionalFile(event, '${newPreviewId}', 'removeThumbnail${thumbnailCount +1 }')">
            </label>
        `;

            document.getElementById('additionalElements').appendChild(newThumbnailBox);

            // get current file
            var inputElement = event.target;
            var newOnchangeFunction = `previewFile(event, '${id}')`;
            // Set the new onchange attribute
            inputElement.setAttribute("onchange", newOnchangeFunction);

        }

        const removeThumbnail = (thumbnailId) => {
            const thumbnailToRemove = document.getElementById(thumbnailId);
            if (thumbnailToRemove) {
                thumbnailToRemove.parentNode.removeChild(thumbnailToRemove);
            }
        }

        const generateCode = () => {
            const code = document.getElementById('barcode');
            code.value = Math.floor(Math.random() * 900000) + 100000;
        }
    </script>

    <!-- color select2 script -->
    <script>
        function formatState(state) {
            if (!state.id) {
                return state.text;
            }
            var $state = $(
                '<span class="d-flex align-items-center"> <span style="background-color:' + state.element.dataset
                .color +
                ';width:20px;height:20px;display:inline-block; border-radius:5px;margin-right:5px;"></span>' + state
                .text + '</span>'
            );
            return $state;
        };

        $(document).ready(function() {
            $('.colorSelect').select2({
                templateResult: formatState
            });
        });
    </script>

    <script>
        correctULTagFromQuill = (str) => {
            if (str) {
                let re = /(<ol><li data-list="bullet">)(.*?)(<\/ol>)/;
                let strArr = str.split(re);

                while (
                    strArr.findIndex((ele) => ele === '<ol><li data-list="bullet">') !== -1
                ) {
                    let index = strArr.findIndex(
                        (ele) => ele === '<ol><li data-list="bullet">'
                    );
                    if (index) {
                        strArr[index] = '<ul><li data-list="bullet">';
                        let endTagIndex = strArr.findIndex((ele) => ele === "</ol>");
                        strArr[endTagIndex] = "</ul>";
                    }
                }
                return strArr.join("");
            }
            return str;
        };

        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{
                        'font': []
                    }],
                    ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    [{
                        'direction': 'rtl'
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    ['link', 'image', 'video', 'formula']
                ]
            }
        });

        quill.on('text-change', function(delta, oldDelta, source) {
            document.getElementById('description').value = correctULTagFromQuill(quill.root.innerHTML);
        });

        function estimateDimensions() {
            try {
                let productName = '';
                const nameInput = document.getElementById('name') || document.querySelector('input[name="name"]');
                if (nameInput && nameInput.value && nameInput.value.trim()) {
                    productName = nameInput.value.trim().toUpperCase();
                } else {
                    productName = "{{ addslashes($product->name ?? '') }}".toUpperCase();
                }

                let hsnCode = "{{ $hsnCode ?? '' }}".trim();

                // Default estimated values for general retail eCommerce package
                let estLength = 28;
                let estWidth  = 20;
                let estHeight = 3;
                let estWeight = 0.30;

                if (productName.includes('DRESS') || productName.includes('FROCK') || productName.includes('GOWN') || productName.includes('MATERNITY')) {
                    estLength = 30;
                    estWidth  = 25;
                    estHeight = 3.5;
                    estWeight = 0.35;
                } else if (productName.includes('SAREE') || productName.includes('LEHENGA') || productName.includes('HEAVY') || productName.includes('SUIT')) {
                    estLength = 38;
                    estWidth  = 30;
                    estHeight = 6;
                    estWeight = 0.85;
                } else if (productName.includes('JEANS') || productName.includes('TROUSER') || productName.includes('PANT') || productName.includes('BOTTOM')) {
                    estLength = 32;
                    estWidth  = 24;
                    estHeight = 4;
                    estWeight = 0.50;
                } else if (productName.includes('SHIRT') || productName.includes('TOP') || productName.includes('KURTI') || productName.includes('BLOUSE')) {
                    estLength = 28;
                    estWidth  = 22;
                    estHeight = 2.5;
                    estWeight = 0.25;
                } else if (productName.includes('T-SHIRT') || productName.includes('TSHIRT') || productName.includes('VEST')) {
                    estLength = 25;
                    estWidth  = 20;
                    estHeight = 2;
                    estWeight = 0.20;
                } else if (productName.includes('JACKET') || productName.includes('COAT') || productName.includes('SWEATER') || productName.includes('HOODIE')) {
                    estLength = 35;
                    estWidth  = 28;
                    estHeight = 6;
                    estWeight = 0.70;
                } else if (productName.includes('LINGERIE') || productName.includes('INNERWEAR') || productName.includes('BRA') || productName.includes('PANTY') || productName.includes('SOCKS')) {
                    estLength = 20;
                    estWidth  = 15;
                    estHeight = 2;
                    estWeight = 0.12;
                } else if (productName.includes('SHOE') || productName.includes('FOOTWEAR') || productName.includes('SANDAL') || productName.includes('BOOT') || productName.includes('SLIPPER')) {
                    estLength = 32;
                    estWidth  = 20;
                    estHeight = 12;
                    estWeight = 0.80;
                } else if (productName.includes('BAG') || productName.includes('HANDBAG') || productName.includes('PURSE') || productName.includes('WALLET')) {
                    estLength = 30;
                    estWidth  = 22;
                    estHeight = 10;
                    estWeight = 0.45;
                } else if (productName.includes('TOY') || productName.includes('GIFT') || productName.includes('GAME')) {
                    estLength = 25;
                    estWidth  = 20;
                    estHeight = 15;
                    estWeight = 0.60;
                } else if (productName.includes('CRADLE') || productName.includes('FURNITURE')) {
                    estLength = 65;
                    estWidth  = 45;
                    estHeight = 30;
                    estWeight = 4.50;
                }

                if (hsnCode) {
                    if (hsnCode.startsWith('64')) {
                        estLength = 32; estWidth = 20; estHeight = 12; estWeight = 0.80;
                    } else if (hsnCode.startsWith('33')) {
                        estLength = 16; estWidth = 12; estHeight = 8;  estWeight = 0.30;
                    } else if (hsnCode.startsWith('42')) {
                        estLength = 30; estWidth = 22; estHeight = 10; estWeight = 0.45;
                    } else if (hsnCode.startsWith('94')) {
                        estLength = 65; estWidth = 45; estHeight = 30; estWeight = 4.50;
                    }
                }

                const lengthInput = document.getElementById('length') || document.querySelector('input[name="length"]');
                const widthInput  = document.getElementById('width')  || document.querySelector('input[name="width"]');
                const heightInput = document.getElementById('height') || document.querySelector('input[name="height"]');
                const weightInput = document.getElementById('weight') || document.querySelector('input[name="weight"]');

                if (lengthInput) lengthInput.value = estLength;
                if (widthInput)  widthInput.value  = estWidth;
                if (heightInput) heightInput.value = estHeight;
                if (weightInput) weightInput.value = estWeight;

                if (typeof Swal !== 'undefined' && Swal.mixin) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Dimensions estimated successfully'
                    });
                }
            } catch (e) {
                console.error('Dimension estimation fallback:', e);
            }
        }
    </script>
@endpush
