@extends('layouts.app')

@section('header-title', __('Product Details'))

@section('content')
    @php
        // Fetch inward product variants
        $rawVariants = $product->designMaster?->inwardProductDesign ?? collect([]);
        
        $inwardProductData = $rawVariants->map(function($iv) use ($product) {
            $hasBarcode = \App\Models\ProductBarcode::where('inward_product_id', $iv->id)->exists();
            $isOnline = (bool)($iv->is_online_product ?? false);

            $colorRecord = \Illuminate\Support\Facades\DB::table('inward_product_colors')
                ->where('inward_product_id', $iv->id)
                ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
                ->select('colors.id', 'colors.name', 'colors.color_code')
                ->first();

            $sizeRecord = \Illuminate\Support\Facades\DB::table('inward_product_sizes')
                ->where('inward_product_id', $iv->id)
                ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
                ->select('sizes.id', 'sizes.name')
                ->first();

            $colorName = $colorRecord?->name ?? 'N/A';
            $colorHex = $colorRecord?->color_code ?? '#e2e8f0';
            $colorId = $colorRecord?->id ?? null;

            $sizeName = $sizeRecord?->name ?? 'N/A';
            $sizeId = $sizeRecord?->id ?? null;

            $qty = (int)($iv->quantity ?? $iv->qty ?? 0);
            $purcRate = (float)($iv->buy_price ?? $iv->net_purc_rate ?? 0);
            $mrp = (float)($iv->mrp ?? $product->mrp ?? $product->price ?? 0);

            // Online discount percent
            $onlineDisc = (float)(
                ($iv->online_discount_percent !== null && $iv->online_discount_percent > 0)
                    ? $iv->online_discount_percent
                    : ($product->online_discount_percent ?? 0)
            );

            // Online selling price after discount
            $onlinePrice = ($onlineDisc > 0)
                ? round($mrp - ($mrp * $onlineDisc / 100), 2)
                : $mrp;

            $amount = $qty * $purcRate;

            return (object)[
                'id' => $iv->id,
                'item_name' => $product->name,
                'design_no' => $iv->designMaster->design_number ?? $product->designMaster?->design_number ?? ($product->code ?? 'N/A'),
                'color_id' => $colorId,
                'color_name' => $colorName,
                'color_hex' => $colorHex,
                'size_id' => $sizeId,
                'size_name' => $sizeName,
                'qty' => $qty,
                'purc_rate' => $purcRate,
                'mrp' => $mrp,
                'online_disc' => $onlineDisc,
                'online_price' => $onlinePrice,
                'amount' => $amount,
                'is_online' => $isOnline,
                'has_barcode' => $hasBarcode,
            ];
        });

        // Filter only active online variants for frontend-style customer pricing
        $onlineVariants = $inwardProductData->filter(fn($v) => $v->is_online && $v->has_barcode)->values();
        $totalOnlineStock = $onlineVariants->isNotEmpty() ? $onlineVariants->sum('qty') : (int)($product->quantity ?? 0);

        // First active variant or fallback to product direct attributes
        $firstOnline = $onlineVariants->first();
        $initialMrp = $firstOnline ? $firstOnline->mrp : (float)($product->mrp ?: $product->price ?: 0);
        $initialPrice = $firstOnline ? $firstOnline->online_price : (float)($product->price ?: $product->mrp ?: 0);
        $initialDisc = $firstOnline ? $firstOnline->online_disc : (float)($product->discount_price > 0 && $product->discount_price < $initialMrp ? round(($initialMrp - $product->discount_price) * 100 / $initialMrp) : 0);

        // Group colors and sizes for interactive front-end selectors
        $availableColors = $onlineVariants->unique('color_name')->filter(fn($v) => $v->color_name !== 'N/A')->values();
        $availableSizes = $onlineVariants->unique('size_name')->filter(fn($v) => $v->size_name !== 'N/A')->values();

        // Product gallery images
        $galleryImages = collect();
        if (!empty($product->thumbnail)) {
            $galleryImages->push((object)[
                'url' => $product->thumbnail,
                'label' => __('Main Thumbnail'),
            ]);
        }
        foreach ($product->medias as $media) {
            $mediaSrc = asset('default/upload.png');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($media->src)) {
                $mediaSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($media->src);
            }
            $galleryImages->push((object)[
                'url' => $mediaSrc,
                'label' => $media->name ?? __('Gallery Photo'),
            ]);
        }
        if ($galleryImages->isEmpty()) {
            $galleryImages->push((object)[
                'url' => asset('default/upload.png'),
                'label' => __('Default Image'),
            ]);
        }

        $mainImageUrl = $galleryImages->first()->url;
    @endphp

    <div class="front-preview-wrapper py-2">
        
        <!-- Top Modern Breadcrumb & Action Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-4 border-bottom">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 align-items-center" style="font-size: 13.5px;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('shop.dashboard.index') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-house-door-fill text-primary"></i>
                            <span>{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('shop.product.index') }}" class="text-decoration-none text-secondary">
                            {{ __('Products') }}
                        </a>
                    </li>
                    @if($product->categories?->first())
                        <li class="breadcrumb-item text-secondary">
                            {{ $product->categories->first()->name }}
                        </li>
                    @endif
                    <li class="breadcrumb-item active text-dark fw-semibold truncate" aria-current="page" style="max-width: 260px;">
                        {{ $product->name }}
                    </li>
                </ol>
            </nav>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Live Website Link -->
                <a href="/products/{{ $product->id }}/details" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold px-3 rounded-pill shadow-xs">
                    <i class="bi bi-box-arrow-up-right me-1.5"></i>{{ __('View Live Store') }}
                </a>

                <!-- Publish / Unpublish Toggle -->
                <a href="{{ route('shop.product.toggle', $product->id) }}" class="btn btn-sm {{ $product->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} fw-semibold px-3 rounded-pill shadow-xs">
                    <i class="bi {{ $product->is_active ? 'bi-eye-slash-fill' : 'bi-eye-fill' }} me-1.5"></i>
                    {{ $product->is_active ? __('Unpublish') : __('Publish') }}
                </a>

                <!-- Edit Button -->
                <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-sm btn-primary fw-semibold px-3.5 rounded-pill shadow-xs">
                    <i class="bi bi-pencil-square me-1.5"></i>{{ __('Edit Product') }}
                </a>

                <!-- Back to list -->
                <a href="{{ route('shop.product.index') }}" class="btn btn-sm btn-light border fw-semibold px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- HERO PRODUCT SHOWCASE (Matching Front Website View) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-body p-3 p-md-4 p-lg-5">
                <div class="row g-4 g-lg-5 align-items-start">
                    
                    <!-- LEFT COLUMN: Product Media Gallery -->
                    <div class="col-lg-5 col-xl-5">
                        <div class="position-sticky" style="top: 20px;">
                            
                            <!-- Main Image Showcase Frame -->
                            <div class="main-image-container position-relative bg-slate-50 rounded-4 border border-light-subtle d-flex align-items-center justify-content-center overflow-hidden mb-3" 
                                 id="zoomContainer" style="height: 420px; background-color: #f8fafc;">
                                
                                <img src="{{ $mainImageUrl }}" id="mainProductShowcase" 
                                     alt="{{ $product->name }}" 
                                     class="main-showcase-img w-100 h-100 object-fit-contain p-3 transition-transform">

                                <!-- Floating Discount Badge on Image -->
                                @if($initialDisc > 0)
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-danger text-white rounded-pill px-3 py-2 fw-bold shadow-sm" id="badgeDiscountImage" style="font-size: 12px; letter-spacing: 0.5px;">
                                            {{ $initialDisc }}% {{ __('OFF') }}
                                        </span>
                                    </div>
                                @endif

                                <!-- Published Status Tag -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    @if($product->is_active)
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1.5 shadow-xs" style="font-size: 11px;">
                                            <i class="bi bi-check-circle-fill me-1"></i>{{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1.5 shadow-xs" style="font-size: 11px;">
                                            <i class="bi bi-pause-circle-fill me-1"></i>{{ __('Draft') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Thumbnails Strip -->
                            <div class="d-flex align-items-center gap-2.5 overflow-x-auto pb-1" id="thumbnailStrip" style="scrollbar-width: thin;">
                                @foreach ($galleryImages as $index => $img)
                                    <div class="thumbnail-item {{ $index === 0 ? 'active' : '' }} rounded-3 border bg-white cursor-pointer position-relative flex-shrink-0"
                                         onclick="switchShowcaseImage('{{ $img->url }}', this)" 
                                         style="width: 72px; height: 72px; padding: 4px; transition: all 0.2s;">
                                        <img src="{{ $img->url }}" alt="{{ $img->label }}" class="w-100 h-100 object-fit-cover rounded-2">
                                    </div>
                                @endforeach

                                <!-- Video Thumbnail if exists -->
                                @if($product->video && !empty($product->video->url))
                                    <div class="thumbnail-item rounded-3 border bg-dark text-white d-flex flex-column align-items-center justify-content-center cursor-pointer flex-shrink-0 position-relative" 
                                         onclick="openVideoModal()" 
                                         style="width: 72px; height: 72px; padding: 4px; transition: all 0.2s;"
                                         data-bs-toggle="tooltip" title="{{ __('Watch Product Video') }}">
                                        <i class="bi bi-play-circle-fill fs-4 text-white"></i>
                                        <span style="font-size: 9px;" class="fw-bold mt-0.5">{{ __('Video') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Fast Specs Highlights -->
                            <div class="mt-4 p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-center justify-content-around text-center small text-secondary">
                                <div>
                                    <i class="bi bi-shield-check fs-5 text-primary d-block mb-1"></i>
                                    <span class="fw-medium">{{ __('Genuine Product') }}</span>
                                </div>
                                <div class="border-start ps-3">
                                    <i class="bi bi-box-seam fs-5 text-primary d-block mb-1"></i>
                                    <span class="fw-medium">{{ __('Quality Packaging') }}</span>
                                </div>
                                <div class="border-start ps-3">
                                    <i class="bi bi-arrow-repeat fs-5 text-primary d-block mb-1"></i>
                                    <span class="fw-medium">{{ __('Verified Inward') }}</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- RIGHT COLUMN: E-Commerce Product Details (Matching Front Website) -->
                    <div class="col-lg-7 col-xl-7">
                        
                        <!-- Brand & SKU Tag Header -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2.5">
                            @if($product->brand)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 11.5px;">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $product->brand->name }}
                                </span>
                            @endif
                            @if($product->code)
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                    SKU: {{ $product->code }}
                                </span>
                            @endif
                            @if($product->designMaster?->design_number)
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                    Design: {{ $product->designMaster->design_number }}
                                </span>
                            @endif
                        </div>

                        <!-- Product Title -->
                        <h1 class="fw-bold text-dark mb-2.5 lh-sm" style="font-size: 1.85rem; letter-spacing: -0.015em;">
                            {{ $product->name }}
                        </h1>

                        <!-- Short Description -->
                        <p class="text-secondary mb-3.5" style="font-size: 14.5px; line-height: 1.65;">
                            {{ $product->short_description ?: __('No short summary specified for this product yet.') }}
                        </p>

                        <!-- Rating & Reviews Bar (Matching Frontend) -->
                        <div class="d-flex flex-wrap align-items-center gap-3 py-3 border-top border-bottom border-light-subtle mb-4">
                            <div class="d-flex align-items-center gap-1.5">
                                <div class="text-warning d-flex gap-0.5" style="font-size: 14px;">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <i class="bi bi-star-fill text-warning"></i>
                                </div>
                                <span class="fw-bold text-dark ms-1" style="font-size: 13.5px;">5.0</span>
                                <span class="text-muted" style="font-size: 13px;">({{ $product->reviews_count ?? 0 }} {{ __('Reviews') }})</span>
                            </div>

                            <div class="vr bg-secondary-subtle" style="height: 16px;"></div>

                            <div class="text-secondary small">
                                <span class="fw-semibold text-dark">{{ $product->orders_count ?? 0 }}</span> {{ __('Sold') }}
                            </div>

                            <div class="vr bg-secondary-subtle" style="height: 16px;"></div>

                            <!-- Live Stock Status Pill -->
                            <div class="d-flex align-items-center gap-1.5">
                                @if($totalOnlineStock > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 11.5px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ __('In Stock') }} ({{ $totalOnlineStock }} {{ $product->unit?->name ?: __('Units') }})
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 11.5px;">
                                        <i class="bi bi-x-circle-fill me-1"></i>{{ __('Out of Stock') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- PRICING HERO SECTION (Identical to Frontend Website) -->
                        <div class="p-3.5 p-md-4 rounded-3 bg-light-subtle border border-light-subtle mb-4">
                            <div class="d-flex flex-wrap align-items-baseline gap-3">
                                <!-- Big Selling Price -->
                                <div class="d-flex align-items-baseline">
                                    <span class="display-6 fw-bold text-primary font-monospace m-0" id="displaySellingPrice">
                                        ₹{{ number_format($initialPrice, 2) }}
                                    </span>
                                </div>

                                <!-- Crossed-out Physical MRP -->
                                <div id="mrpWrapper" class="{{ $initialMrp > $initialPrice ? '' : 'd-none' }}">
                                    <del class="text-muted fs-4 font-monospace" id="displayMrp">
                                        ₹{{ number_format($initialMrp, 2) }}
                                    </del>
                                </div>

                                <!-- Discount Percentage Pill -->
                                <div id="discWrapper" class="{{ $initialDisc > 0 ? '' : 'd-none' }}">
                                    <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-2xs" id="displayDiscount" style="font-size: 13px;">
                                        {{ $initialDisc }}% {{ __('OFF') }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-muted small mt-2 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle text-primary"></i>
                                <span>{{ __('Prices include all applicable taxes. Physical MRP printed on item tag.') }}</span>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- FRONTEND VARIANT SELECTION (Colors & Sizes Dynamic Pickers) -->
                        <!-- ============================================================ -->
                        @if($inwardProductData->isNotEmpty())
                            <div class="variant-section-box pt-4 pb-2 border-top border-light-subtle my-4" id="variantSelectorBox">
                                
                                <!-- 1. COLOR SELECTOR -->
                                @if($availableColors->isNotEmpty())
                                    <div class="variant-group mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.6px;">
                                                    {{ __('Color') }}:
                                                </span>
                                                <span class="fw-bold text-dark fs-6" id="selectedColorLabel">
                                                    {{ $firstOnline?->color_name ?? $availableColors->first()->color_name }}
                                                </span>
                                            </div>
                                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                                {{ $availableColors->count() }} {{ $availableColors->count() == 1 ? __('Color') : __('Colors') }}
                                            </span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2.5 pt-1" id="colorChipsContainer">
                                            @foreach($availableColors as $c)
                                                <button type="button" 
                                                        class="btn color-picker-btn {{ ($firstOnline && $firstOnline->color_name === $c->color_name) ? 'active' : '' }} d-inline-flex align-items-center gap-2 rounded-pill px-3.5 py-2 border bg-white"
                                                        data-color-name="{{ $c->color_name }}"
                                                        onclick="selectColor('{{ $c->color_name }}', this)">
                                                    <span class="color-swatch-circle rounded-circle border shadow-2xs" style="width: 15px; height: 15px; background-color: {{ $c->color_hex }};"></span>
                                                    <span class="fw-semibold text-dark" style="font-size: 13.5px;">{{ $c->color_name }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- 2. SIZE SELECTOR -->
                                @if($availableSizes->isNotEmpty())
                                    <div class="variant-group mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.6px;">
                                                    {{ __('Size') }}:
                                                </span>
                                                <span class="fw-bold text-dark fs-6" id="selectedSizeLabel">
                                                    {{ $firstOnline?->size_name ?? $availableSizes->first()->size_name }}
                                                </span>
                                            </div>
                                            <span class="text-muted" style="font-size: 12px;">{{ __('Select variant size to update pricing') }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 pt-1" id="sizePillsContainer">
                                            @foreach($availableSizes as $s)
                                                <button type="button" 
                                                        class="btn size-picker-btn {{ ($firstOnline && $firstOnline->size_name === $s->size_name) ? 'active' : '' }} rounded-3 px-3 py-2 border bg-white fw-bold font-monospace"
                                                        data-size-name="{{ $s->size_name }}"
                                                        onclick="selectSize('{{ $s->size_name }}', this)">
                                                    {{ $s->size_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Selected Variant Detail Info Card (Clean, airy, modern design) -->
                                <div class="selected-variant-card rounded-3 p-3.5 mt-4 mb-2" id="selectedVariantInfoBanner">
                                    <div class="row g-3 align-items-center text-center text-sm-start">
                                        <div class="col-6 col-sm-3">
                                            <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">{{ __('SELECTED COLOR') }}</span>
                                            <span class="fw-bold text-dark fs-6" id="bannerColor">{{ $firstOnline?->color_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6 col-sm-3 border-start border-light-subtle">
                                            <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">{{ __('SELECTED SIZE') }}</span>
                                            <span class="fw-bold text-dark fs-6 font-monospace" id="bannerSize">{{ $firstOnline?->size_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6 col-sm-3 border-start border-light-subtle">
                                            <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">{{ __('PHYSICAL MRP') }}</span>
                                            <span class="fw-bold text-secondary fs-6 font-monospace" id="bannerMrp">₹{{ number_format($initialMrp, 2) }}</span>
                                        </div>
                                        <div class="col-6 col-sm-3 border-start border-light-subtle">
                                            <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.5px;">{{ __('ONLINE SELLING') }}</span>
                                            <span class="fw-bold text-primary fs-6 font-monospace" id="bannerSelling">₹{{ number_format($initialPrice, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif

                        <!-- QUICK ACTIONS BAR -->
                        <div class="action-buttons-bar d-flex flex-wrap align-items-center gap-3 pt-3 mt-2 border-top border-light-subtle">
                            <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square fs-6"></i>
                                <span>{{ __('Edit Product Details') }}</span>
                            </a>

                            <a href="/products/{{ $product->id }}/details" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-up-right fs-6"></i>
                                <span>{{ __('Open in Live Webstore') }}</span>
                            </a>

                            <a href="{{ route('shop.product.toggle', $product->id) }}" class="btn {{ $product->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi {{ $product->is_active ? 'bi-eye-slash' : 'bi-eye' }} fs-6"></i>
                                <span>{{ $product->is_active ? __('Unpublish') : __('Publish') }}</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- BOTTOM SECTION: FRONTEND-STYLE TABS (Description, Variants, Specs, SEO, Video) -->
        <!-- ============================================================ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            
            <!-- Tab Headers -->
            <div class="card-header bg-white border-bottom px-4 pt-3 pb-0">
                <ul class="nav nav-tabs border-0 front-tabs-nav gap-2 gap-md-4" id="productDetailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold pb-3 px-2 border-0 bg-transparent" id="tab-about-btn" data-bs-toggle="tab" data-bs-target="#tab-about" type="button" role="tab">
                            <i class="bi bi-file-text me-1.5 text-primary"></i>{{ __('About Product') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold pb-3 px-2 border-0 bg-transparent" id="tab-variants-btn" data-bs-toggle="tab" data-bs-target="#tab-variants" type="button" role="tab">
                            <i class="bi bi-table me-1.5 text-primary"></i>{{ __('Inward Variants & Inventory') }}
                            <span class="badge bg-primary-subtle text-primary rounded-pill ms-1 px-2" style="font-size: 11px;">{{ $inwardProductData->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold pb-3 px-2 border-0 bg-transparent" id="tab-specs-btn" data-bs-toggle="tab" data-bs-target="#tab-specs" type="button" role="tab">
                            <i class="bi bi-sliders me-1.5 text-primary"></i>{{ __('Specifications & Shipping') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold pb-3 px-2 border-0 bg-transparent" id="tab-seo-btn" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab">
                            <i class="bi bi-google me-1.5 text-primary"></i>{{ __('SEO & Search Preview') }}
                        </button>
                    </li>
                    @if($product->video && !empty($product->video->url))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold pb-3 px-2 border-0 bg-transparent" id="tab-video-btn" data-bs-toggle="tab" data-bs-target="#tab-video" type="button" role="tab">
                                <i class="bi bi-film me-1.5 text-primary"></i>{{ __('Product Video') }}
                            </button>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Tab Contents -->
            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="productDetailTabContent">
                    
                    <!-- TAB 1: About Product (Description) -->
                    <div class="tab-pane fade show active" id="tab-about" role="tabpanel">
                        <div class="product-description-content text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            @if(!empty($product->description))
                                {!! $product->description !!}
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-file-earmark-text display-4 text-muted mb-3 d-block"></i>
                                    <h6 class="fw-semibold text-dark">{{ __('No detailed description provided.') }}</h6>
                                    <p class="text-muted small mb-3">{{ __('Add rich product details, material care, and features to help customers decide.') }}</p>
                                    <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="bi bi-pencil me-1"></i>{{ __('Add Description') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- TAB 2: Inward Variants & Inventory Table -->
                    <div class="tab-pane fade" id="tab-variants" role="tabpanel">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark m-0">{{ __('Variant Pricing & Stock Breakdown') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Physical tag MRP, online discount percentages, and final online selling rates.') }}</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                {{ $onlineVariants->count() }} {{ __('Variants Live Online') }}
                            </span>
                        </div>

                        @if($inwardProductData->isNotEmpty())
                            <div class="table-responsive border rounded-3 overflow-hidden shadow-2xs">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light border-bottom">
                                        <tr class="text-muted">
                                            <th class="text-center" style="width: 45px;">#</th>
                                            <th>{{ __('Design No') }}</th>
                                            <th>{{ __('Color') }}</th>
                                            <th>{{ __('Size') }}</th>
                                            <th class="text-center">{{ __('Stock Qty') }}</th>
                                            <th class="text-end">{{ __('Purc Rate') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                            <th class="text-end">{{ __('Physical MRP') }}</th>
                                            <th class="text-center">{{ __('Online Disc %') }}</th>
                                            <th class="text-end text-primary fw-bold">{{ __('Online Price') }}</th>
                                            <th class="text-center">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalSumQty = 0;
                                            $totalSumAmount = 0;
                                            $totalSumMrp = 0;
                                            $totalSumOnline = 0;
                                        @endphp
                                        @foreach($inwardProductData as $v)
                                            @php
                                                $totalSumQty += $v->qty;
                                                $totalSumAmount += $v->amount;
                                                $totalSumMrp += $v->mrp;
                                                $totalSumOnline += $v->online_price;
                                            @endphp
                                            <tr class="{{ !$v->is_online ? 'table-light opacity-75' : '' }}">
                                                <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace">{{ $v->design_no }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-inline-flex align-items-center gap-1.5">
                                                        <span class="rounded-circle border" style="width: 12px; height: 12px; background-color: {{ $v->color_hex }};"></span>
                                                        <span class="fw-semibold text-dark">{{ $v->color_name }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $v->size_name }}</span>
                                                </td>
                                                <td class="text-center font-monospace fw-bold text-dark">{{ $v->qty }}</td>
                                                <td class="text-end font-monospace text-secondary">₹{{ number_format($v->purc_rate, 2) }}</td>
                                                <td class="text-end font-monospace fw-semibold text-dark">₹{{ number_format($v->amount, 2) }}</td>
                                                <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format($v->mrp, 2) }}</td>
                                                <td class="text-center">
                                                    @if($v->online_disc > 0)
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace">
                                                            {{ number_format($v->online_disc, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end font-monospace fw-bold text-primary fs-6">
                                                    ₹{{ number_format($v->online_price, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($v->is_online)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                            <i class="bi bi-check-circle-fill me-1"></i>{{ __('Online') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                            {{ __('Offline') }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light border-top fw-bold" style="font-size: 13px;">
                                        <tr>
                                            <td colspan="4" class="text-end text-muted">{{ __('Total Sum:') }}</td>
                                            <td class="text-center font-monospace text-dark">{{ $totalSumQty }}</td>
                                            <td class="text-end text-muted">-</td>
                                            <td class="text-end font-monospace text-dark">₹{{ number_format($totalSumAmount, 2) }}</td>
                                            <td class="text-end font-monospace text-dark">₹{{ number_format($totalSumMrp, 2) }}</td>
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-end font-monospace text-primary fs-6">₹{{ number_format($totalSumOnline, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-info-circle me-1"></i> {{ __('No inward variants found for this product.') }}
                            </div>
                        @endif
                    </div>

                    <!-- TAB 3: Specifications & Shipping Dimensions -->
                    <div class="tab-pane fade" id="tab-specs" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Delivery Dimensions Cards -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-truck text-primary"></i>
                                    <span>{{ __('Package & Shipping Dimensions') }}</span>
                                </h6>
                                <div class="row g-2.5 text-center">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border rounded-3 bg-light-subtle">
                                            <span class="text-muted d-block small mb-1">{{ __('Length') }}</span>
                                            <span class="fw-bold text-dark font-monospace fs-6">{{ $product->length ? $product->length . ' cm' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border rounded-3 bg-light-subtle">
                                            <span class="text-muted d-block small mb-1">{{ __('Width') }}</span>
                                            <span class="fw-bold text-dark font-monospace fs-6">{{ $product->width ? $product->width . ' cm' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border rounded-3 bg-light-subtle">
                                            <span class="text-muted d-block small mb-1">{{ __('Height') }}</span>
                                            <span class="fw-bold text-dark font-monospace fs-6">{{ $product->height ? $product->height . ' cm' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border rounded-3 bg-light-subtle">
                                            <span class="text-muted d-block small mb-1">{{ __('Weight') }}</span>
                                            <span class="fw-bold text-dark font-monospace fs-6">{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Attributes & Hierarchy -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-diagram-3 text-primary"></i>
                                    <span>{{ __('Product Categorization') }}</span>
                                </h6>
                                <div class="p-3 border rounded-3 bg-white">
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 13.5px;">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted" style="width: 40%;">{{ __('Category:') }}</td>
                                                <td class="fw-semibold text-dark">{{ $product->categories?->pluck('name')->implode(', ') ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">{{ __('Sub Categories:') }}</td>
                                                <td class="fw-semibold text-dark">{{ $product->subcategories?->pluck('name')->implode(', ') ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">{{ __('Brand:') }}</td>
                                                <td class="fw-semibold text-dark">{{ $product->brand?->name ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">{{ __('Unit:') }}</td>
                                                <td class="fw-semibold text-dark">{{ $product->unit?->name ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">{{ __('Design Number:') }}</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary border font-monospace">{{ $product->designMaster?->design_number ?: 'N/A' }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TAB 4: SEO Information & Google Search Snippet Preview -->
                    <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- Google SERP Snippet Preview -->
                            <div class="col-lg-7">
                                <h6 class="fw-bold text-dark mb-2.5 d-flex align-items-center gap-2">
                                    <i class="bi bi-search text-primary"></i>
                                    <span>{{ __('Google Search Snippet Preview') }}</span>
                                </h6>
                                <div class="p-4 border rounded-3 bg-light-subtle shadow-2xs">
                                    <div class="text-muted small mb-1" style="font-size: 12px;">
                                        https://{{ request()->getHost() }} &rsaquo; products &rsaquo; {{ $product->id }}
                                    </div>
                                    <h5 class="text-primary fw-semibold mb-1" style="cursor: pointer; text-decoration: underline; font-size: 18px;">
                                        {{ $product->meta_title ?: $product->name }}
                                    </h5>
                                    <p class="text-secondary small mb-0" style="line-height: 1.5; font-size: 13px;">
                                        {{ $product->meta_description ?: ($product->short_description ?: __('Explore high-quality products at great value on our online catalog with fast delivery.')) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Meta Fields View -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-dark mb-2.5">{{ __('Meta Parameters') }}</h6>
                                <div class="mb-3">
                                    <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Title') }}</span>
                                    <div class="p-2 border rounded bg-light-subtle font-monospace text-dark small">
                                        {{ $product->meta_title ?: __('N/A') }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Description') }}</span>
                                    <div class="p-2 border rounded bg-light-subtle text-dark small">
                                        {{ $product->meta_description ?: __('N/A') }}
                                    </div>
                                </div>
                                <div>
                                    <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Keywords') }}</span>
                                    <div class="p-2 border rounded bg-light-subtle text-dark small">
                                        @if(!empty($product->meta_keywords))
                                            @php
                                                $keywords = is_array($product->meta_keywords) ? $product->meta_keywords : explode(',', $product->meta_keywords);
                                            @endphp
                                            @foreach($keywords as $kw)
                                                <span class="badge bg-secondary-subtle text-secondary border me-1 mb-1">{{ trim($kw) }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted italic">{{ __('N/A') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TAB 5: Product Video Player -->
                    @if($product->video && !empty($product->video->url))
                        <div class="tab-pane fade" id="tab-video" role="tabpanel">
                            @php
                                $vType = $product->video->type ?? 'file';
                                $vUrl = $product->video->url;
                                $embedUrl = '';

                                if ($vType === 'file') {
                                    $embedUrl = asset($vUrl);
                                } else if (str_contains($vUrl, '<iframe')) {
                                    preg_match('/src=["\']([^"\']+)["\']/i', $vUrl, $matches);
                                    $embedUrl = $matches[1] ?? $vUrl;
                                } else if ($vType === 'youtube') {
                                    if (str_contains($vUrl, 'v=')) {
                                        $parts = explode('v=', $vUrl);
                                        $vId = explode('&', $parts[1])[0];
                                        $embedUrl = "https://www.youtube.com/embed/{$vId}";
                                    } else if (str_contains($vUrl, 'youtu.be/')) {
                                        $parts = explode('youtu.be/', $vUrl);
                                        $vId = explode('?', $parts[1])[0];
                                        $embedUrl = "https://www.youtube.com/embed/{$vId}";
                                    } else {
                                        $embedUrl = $vUrl;
                                    }
                                } else if ($vType === 'vimeo') {
                                    $vId = preg_replace('/[^0-9]/', '', $vUrl);
                                    $embedUrl = !empty($vId) ? "https://player.vimeo.com/video/{$vId}" : $vUrl;
                                } else {
                                    $embedUrl = $vUrl;
                                }
                            @endphp

                            <div class="card border rounded-3 bg-dark overflow-hidden mx-auto" style="max-width: 800px;">
                                @if($vType === 'file')
                                    <video controls class="w-100" style="max-height: 460px;" src="{{ $embedUrl }}"></video>
                                @else
                                    <div class="ratio ratio-16x9">
                                        <iframe src="{{ $embedUrl }}" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

    <!-- Video Modal for direct play from thumbnail click -->
    @if($product->video && !empty($product->video->url))
        <div class="modal fade" id="productVideoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 bg-dark rounded-3 overflow-hidden">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title text-white small">{{ $product->name }} - {{ __('Video') }}</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="ratio ratio-16x9 rounded overflow-hidden">
                            @if(($product->video->type ?? '') === 'file')
                                <video controls class="w-100 h-100" src="{{ asset($product->video->url) }}"></video>
                            @else
                                <iframe src="{{ $embedUrl }}" frameborder="0" allowfullscreen></iframe>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('css')
    <style>
        .front-preview-wrapper {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .main-image-container {
            transition: all 0.3s ease;
        }

        .main-showcase-img {
            transition: transform 0.3s ease;
        }

        .main-image-container:hover .main-showcase-img {
            transform: scale(1.04);
        }

        .thumbnail-item {
            border: 2px solid #e2e8f0 !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .thumbnail-item:hover {
            border-color: #94a3b8 !important;
            transform: translateY(-2px);
        }

        .thumbnail-item.active {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
            transform: translateY(-2px);
        }

        /* Color & Size Pickers (matching frontend Vue page with enhanced spacing) */
        .variant-section-box {
            border-top: 1px solid #eef2f6 !important;
        }

        .variant-group {
            margin-bottom: 22px;
        }

        .color-picker-btn {
            border: 1.5px solid #e2e8f0 !important;
            padding: 8px 18px !important;
            border-radius: 9999px !important;
            background-color: #ffffff !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .color-picker-btn:hover {
            border-color: #94a3b8 !important;
            background-color: #f8fafc !important;
            transform: translateY(-1px);
        }

        .color-picker-btn.active {
            border-color: #0d6efd !important;
            background-color: #eff6ff !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
        }

        .size-picker-btn {
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            min-width: 50px !important;
            height: 44px !important;
            padding: 0 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #334155 !important;
            background-color: #ffffff !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .size-picker-btn:hover {
            border-color: #0d6efd !important;
            color: #0d6efd !important;
            background-color: #f8fafc !important;
            transform: translateY(-1px);
        }

        .size-picker-btn.active {
            border-color: #0d6efd !important;
            background-color: #0d6efd !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3) !important;
        }

        /* Selected Variant Summary Card */
        .selected-variant-card {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            padding: 16px 20px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
        }

        /* Action Buttons Bar */
        .action-buttons-bar {
            border-top: 1px solid #eef2f6 !important;
        }

        .action-buttons-bar .btn {
            height: 44px !important;
            padding: 0 24px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            transition: all 0.2s ease;
        }

        .action-buttons-bar .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Modern Tabs Navigation */
        .front-tabs-nav .nav-link {
            color: #64748b;
            position: relative;
            font-size: 14.5px;
            transition: all 0.2s ease;
        }

        .front-tabs-nav .nav-link:hover {
            color: #0d6efd;
        }

        .front-tabs-nav .nav-link.active {
            color: #0d6efd !important;
            font-weight: 700;
        }

        .front-tabs-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #0d6efd;
            border-radius: 3px 3px 0 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Store online variants in JSON for dynamic interactive pricing
        const productVariants = @json($onlineVariants);
        
        let currentColor = "{{ $firstOnline?->color_name ?? ($availableColors->first()->color_name ?? '') }}";
        let currentSize = "{{ $firstOnline?->size_name ?? ($availableSizes->first()->size_name ?? '') }}";

        // Switch main showcase photo
        function switchShowcaseImage(src, elem) {
            const showcaseImg = document.getElementById('mainProductShowcase');
            if (showcaseImg) {
                showcaseImg.style.opacity = '0.4';
                setTimeout(() => {
                    showcaseImg.src = src;
                    showcaseImg.style.opacity = '1';
                }, 120);
            }

            $('.thumbnail-item').removeClass('active');
            if (elem) {
                $(elem).addClass('active');
            }
        }

        // Open Video Modal
        function openVideoModal() {
            const videoModal = new bootstrap.Modal(document.getElementById('productVideoModal'));
            if (videoModal) {
                videoModal.show();
            }
        }

        // Interactive Variant Selection: Color
        function selectColor(colorName, elem) {
            currentColor = colorName;
            $('.color-picker-btn').removeClass('active');
            $(elem).addClass('active');

            const colorLabel = document.getElementById('selectedColorLabel');
            if (colorLabel) colorLabel.innerText = colorName;

            updateVariantDisplay();
        }

        // Interactive Variant Selection: Size
        function selectSize(sizeName, elem) {
            currentSize = sizeName;
            $('.size-picker-btn').removeClass('active');
            $(elem).addClass('active');

            const sizeLabel = document.getElementById('selectedSizeLabel');
            if (sizeLabel) sizeLabel.innerText = sizeName;

            updateVariantDisplay();
        }

        // Calculate and update the dynamic pricing & banner
        function updateVariantDisplay() {
            if (!productVariants || productVariants.length === 0) return;

            // Find matching variant
            let matched = productVariants.find(v => 
                (currentColor === '' || v.color_name === currentColor) && 
                (currentSize === '' || v.size_name === currentSize)
            );

            // Fallback to color match or size match
            if (!matched) {
                matched = productVariants.find(v => v.size_name === currentSize) || productVariants[0];
            }

            if (matched) {
                const sellingPrice = parseFloat(matched.online_price || matched.mrp || 0);
                const mrp = parseFloat(matched.mrp || 0);
                const discount = parseFloat(matched.online_disc || 0);

                // Update Hero Pricing
                const sellingElem = document.getElementById('displaySellingPrice');
                if (sellingElem) sellingElem.innerText = '₹' + sellingPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const mrpElem = document.getElementById('displayMrp');
                const mrpWrap = document.getElementById('mrpWrapper');
                if (mrpElem && mrpWrap) {
                    if (mrp > sellingPrice) {
                        mrpElem.innerText = '₹' + mrp.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        mrpWrap.classList.remove('d-none');
                    } else {
                        mrpWrap.classList.add('d-none');
                    }
                }

                const discElem = document.getElementById('displayDiscount');
                const discWrap = document.getElementById('discWrapper');
                const imgDisc = document.getElementById('badgeDiscountImage');
                if (discElem && discWrap) {
                    if (discount > 0) {
                        discElem.innerText = Math.round(discount) + '% OFF';
                        discWrap.classList.remove('d-none');
                        if (imgDisc) {
                            imgDisc.innerText = Math.round(discount) + '% OFF';
                            imgDisc.classList.remove('d-none');
                        }
                    } else {
                        discWrap.classList.add('d-none');
                        if (imgDisc) imgDisc.classList.add('d-none');
                    }
                }

                // Update info banner
                const bColor = document.getElementById('bannerColor');
                if (bColor) bColor.innerText = matched.color_name || 'N/A';

                const bSize = document.getElementById('bannerSize');
                if (bSize) bSize.innerText = matched.size_name || 'N/A';

                const bMrp = document.getElementById('bannerMrp');
                if (bMrp) bMrp.innerText = '₹' + mrp.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const bSelling = document.getElementById('bannerSelling');
                if (bSelling) bSelling.innerText = '₹' + sellingPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    </script>
@endpush
