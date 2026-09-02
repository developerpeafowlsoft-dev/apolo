@extends('layouts.app')

@section('header-title', __('Product Details'))

@section('content')
    @php
        // Filter inward product variants to ONLY Sell Online enabled with generated barcodes
        $rawVariants = $product->designMaster?->inwardProductDesign ?? collect([]);
        $inwardVariants = $rawVariants->filter(function($iv) {
            $hasBarcode = \App\Models\ProductBarcode::where('inward_product_id', $iv->id)->exists();
            $isOnline = (bool)($iv->is_online_product ?? false);
            return $hasBarcode && $isOnline;
        })->values();

        $inwardProductData = $inwardVariants->map(function($iv) use ($product) {
            $colorData = \Illuminate\Support\Facades\DB::table('inward_product_colors')
                ->where('inward_product_id', $iv->id)
                ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
                ->pluck('colors.name')->filter()->implode(', ');
            $sizeData = \Illuminate\Support\Facades\DB::table('inward_product_sizes')
                ->where('inward_product_id', $iv->id)
                ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
                ->pluck('sizes.name')->filter()->implode(', ');

            $qty = (int)($iv->quantity ?? $iv->qty ?? 0);
            $purcRate = (float)($iv->buy_price ?? $iv->net_purc_rate ?? 0);
            $mrp = (float)($iv->mrp ?? $iv->price ?? 0);
            $price = (float)($iv->price ?? $iv->mrp ?? 0);
            $discPercent = (float)($iv->discount_price ?? 0);
            $amount = $qty * $purcRate;

            return (object)[
                'id' => $iv->id,
                'item_name' => $product->name,
                'design_no' => $iv->designMaster->design_number ?? $product->designMaster?->design_number ?? 'N/A',
                'colors' => !empty($colorData) ? $colorData : 'N/A',
                'sizes' => !empty($sizeData) ? $sizeData : 'N/A',
                'qty' => $qty,
                'purc_rate' => $purcRate,
                'price' => $price,
                'amount' => $amount,
                'disc_percent' => $discPercent,
                'mrp' => $mrp,
            ];
        });

        // Calculated Total Stock from online variants
        $displayStock = ($inwardVariants->count() > 0) ? $inwardProductData->sum('qty') : ($product->quantity ?? 0);
        $firstVariant = $inwardProductData->first();
        $displayPrice = $firstVariant ? $firstVariant->price : ($product->price ?? 0);
        $displayMrp = $firstVariant ? $firstVariant->mrp : ($product->mrp ?? $product->price ?? 0);
        $displayDisc = $firstVariant ? $firstVariant->disc_percent : 0;
    @endphp

    <!-- Top Action Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-dark m-0">{{ $product->name }}</h4>
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
            <p class="text-muted mb-0 small mt-1">{{ __('Comprehensive view of online variants, inventory stock, media, pricing, and SEO parameters.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-primary btn-sm fw-semibold px-3 rounded-2">
                <i class="bi bi-pencil-square me-1"></i> {{ __('Edit Product') }}
            </a>
            <a href="{{ route('shop.product.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 rounded-2">
                <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Products') }}
            </a>
        </div>
    </div>

    <!-- Hero Product Section (Images & Core Info) -->
    <div class="card border-0 shadow-2xs rounded-3 bg-white mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-start">
                
                <!-- Left: Media Gallery Showcase -->
                <div class="col-lg-5">
                    <div class="border rounded-3 p-2 bg-light-subtle text-center overflow-hidden position-relative mb-3" style="height: 320px;">
                        <img src="{{ $product->thumbnail ?? asset('default/upload.png') }}" id="mainProductShowcase" 
                            alt="{{ $product->name }}" class="w-100 h-100 object-fit-contain rounded-2">
                    </div>

                    <!-- Thumbnails Strip -->
                    <div class="d-flex flex-wrap gap-2 justify-content-start">
                        @if(!empty($product->thumbnail))
                            <div class="border rounded-2 p-1 bg-white cursor-pointer thumbnail-opt active-thumb" onclick="swapMainImage('{{ $product->thumbnail }}', this)" style="width: 60px; height: 60px;">
                                <img src="{{ $product->thumbnail }}" class="w-100 h-100 object-fit-cover rounded-1">
                            </div>
                        @endif
                        @foreach ($product->medias as $media)
                            @php
                                $mediaSrc = asset('default/upload.png');
                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($media->src)) {
                                    $mediaSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($media->src);
                                }
                            @endphp
                            <div class="border rounded-2 p-1 bg-white cursor-pointer thumbnail-opt" onclick="swapMainImage('{{ $mediaSrc }}', this)" style="width: 60px; height: 60px;">
                                <img src="{{ $mediaSrc }}" class="w-100 h-100 object-fit-cover rounded-1">
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right: Core Specs & Quick Summary -->
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        @if($product->brand)
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                <i class="bi bi-tag-fill me-1"></i>{{ $product->brand->name }}
                            </span>
                        @endif
                        @if($product->categories?->first())
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                {{ $product->categories->pluck('name')->implode(', ') }}
                            </span>
                        @endif
                        @if($product->unit)
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                {{ $product->unit->name }}
                            </span>
                        @endif
                    </div>

                    <h3 class="fw-bold text-dark mb-2">{{ $product->name }}</h3>
                    <p class="text-secondary small mb-3" style="line-height: 1.6;">{{ $product->short_description ?: __('No short description available.') }}</p>

                    <!-- Key Pricing Banner -->
                    <div class="p-3 bg-light-subtle rounded-3 border mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <span class="text-muted small d-block mb-0.5">{{ __('Selling Price') }}</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h3 class="fw-bold text-primary font-monospace m-0">₹{{ number_format($displayPrice, 2) }}</h3>
                                @if($displayMrp > $displayPrice)
                                    <del class="text-muted font-monospace small">₹{{ number_format($displayMrp, 2) }}</del>
                                @endif
                                @if($displayDisc > 0)
                                    <span class="badge bg-danger px-2 py-0.5 rounded-pill" style="font-size: 11px;">{{ $displayDisc }}% {{ __('OFF') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end border-start ps-3">
                            <span class="text-muted small d-block mb-0.5">{{ __('Total Online Stock') }}</span>
                            <span class="fw-bold text-dark font-monospace fs-5">{{ $displayStock }} {{ $product->unit?->name ?: __('Units') }}</span>
                        </div>
                    </div>

                    <!-- Quick Details Grid -->
                    <div class="row g-2 mb-3 small text-secondary">
                        <div class="col-sm-6">
                            <div class="p-2 border rounded-2 bg-white">
                                <strong class="text-dark">{{ __('Sub Categories:') }}</strong> 
                                <span>{{ $product->subcategories?->pluck('name')->implode(', ') ?: 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2 border rounded-2 bg-white">
                                <strong class="text-dark">{{ __('Online Variants:') }}</strong> 
                                <span class="badge bg-primary-subtle text-primary font-monospace ms-1">{{ $inwardVariants->count() }} {{ __('Active') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="/products/{{ $product->id }}/details" target="_blank" class="btn btn-outline-primary btn-sm rounded-2 px-3 fw-semibold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('View Live Website') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 2-Column Details Dashboard (Left: Description, Variants Table, Delivery, SEO | Right: Specs & Video) -->
    <div class="row g-3">
        
        <!-- LEFT COLUMN (8 cols) -->
        <div class="col-lg-8">
            
            <!-- Full Description Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-file-text text-primary fs-6"></i>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Full Product Description') }}</h6>
                </div>
                <div class="card-body p-3 text-secondary" style="line-height: 1.7; font-size: 13.5px;">
                    @if(!empty($product->description))
                        {!! $product->description !!}
                    @else
                        <p class="text-muted italic mb-0">{{ __('No full description entered for this product.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Inward Variants & Stock Table Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-currency-rupee text-primary fs-6"></i>
                        <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Inward Variant Pricing & Inventory') }}</h6>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                        {{ $inwardVariants->count() }} {{ $inwardVariants->count() == 1 ? __('Online Variant') : __('Online Variants') }}
                    </span>
                </div>
                <div class="card-body p-3">
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
                            <i class="bi bi-info-circle me-1"></i> {{ __('No online-enabled inward variants linked to this product.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery & Dimensions Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-primary fs-6"></i>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Delivery & Dimensions') }}</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-2.5 border rounded-2 bg-light-subtle">
                                <span class="text-muted d-block small mb-1">{{ __('Length') }}</span>
                                <span class="fw-bold text-dark font-monospace">{{ $product->length ? $product->length . ' cm' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-2.5 border rounded-2 bg-light-subtle">
                                <span class="text-muted d-block small mb-1">{{ __('Width') }}</span>
                                <span class="fw-bold text-dark font-monospace">{{ $product->width ? $product->width . ' cm' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-2.5 border rounded-2 bg-light-subtle">
                                <span class="text-muted d-block small mb-1">{{ __('Height') }}</span>
                                <span class="fw-bold text-dark font-monospace">{{ $product->height ? $product->height . ' cm' : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-2.5 border rounded-2 bg-light-subtle">
                                <span class="text-muted d-block small mb-1">{{ __('Weight') }}</span>
                                <span class="fw-bold text-dark font-monospace">{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Information Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-search text-primary fs-6"></i>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('SEO Information') }}</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Title') }}</span>
                        <div class="p-2 border rounded-2 bg-light-subtle font-monospace text-dark small">
                            {{ $product->meta_title ?: __('N/A') }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Description') }}</span>
                        <div class="p-2 border rounded-2 bg-light-subtle text-dark small">
                            {{ $product->meta_description ?: __('N/A') }}
                        </div>
                    </div>
                    <div>
                        <span class="text-muted small d-block mb-1 fw-semibold">{{ __('Meta Keywords') }}</span>
                        <div class="p-2 border rounded-2 bg-light-subtle text-dark small">
                            @if(!empty($product->meta_keywords))
                                @php
                                    $keywords = is_array($product->meta_keywords) ? $product->meta_keywords : explode(',', $product->meta_keywords);
                                @endphp
                                @foreach($keywords as $kw)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1 mb-1">{{ trim($kw) }}</span>
                                @endforeach
                            @else
                                <span class="text-muted italic">{{ __('N/A') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN (4 cols) -->
        <div class="col-lg-4">
            
            <!-- Product Identity & Specifications Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-6"></i>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Specifications & Identity') }}</h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 13px;">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%;">{{ __('Product SKU:') }}</td>
                                <td class="fw-bold text-dark font-monospace">{{ $product->code ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ __('Design No:') }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">{{ $product->designMaster?->design_number ?: 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ __('Category:') }}</td>
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
                                <td class="text-muted align-top pt-2">{{ __('Colors:') }}</td>
                                <td class="pt-1">
                                    @forelse($product->colors as $c)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle me-1 mb-1">{{ $c->name }}</span>
                                    @empty
                                        <span class="text-muted small">N/A</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted align-top pt-2">{{ __('Sizes:') }}</td>
                                <td class="pt-1">
                                    @forelse($product->sizes as $s)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle me-1 mb-1">{{ $s->name }}</span>
                                    @empty
                                        <span class="text-muted small">N/A</span>
                                    @endforelse
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Video Preview Card -->
            <div class="card border-0 shadow-2xs rounded-3 bg-white mb-3">
                <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-film text-primary fs-6"></i>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 13.5px;">{{ __('Product Video') }}</h6>
                </div>
                <div class="card-body p-3">
                    @if($product->video && !empty($product->video->url))
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

                        <div class="overflow-hidden rounded-2 bg-dark">
                            @if($vType === 'file')
                                <video controls class="w-100 rounded-2" style="max-height: 220px;" src="{{ $embedUrl }}"></video>
                            @else
                                <iframe width="100%" height="220" src="{{ $embedUrl }}" frameborder="0" allowfullscreen class="rounded-2"></iframe>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-camera-video me-1"></i> {{ __('No video attached to this product.') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@push('css')
    <style>
        .thumbnail-opt {
            transition: all 0.2s ease-in-out;
        }
        .thumbnail-opt:hover, .active-thumb {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function swapMainImage(src, elem) {
            document.getElementById('mainProductShowcase').src = src;
            $('.thumbnail-opt').removeClass('active-thumb');
            $(elem).addClass('active-thumb');
        }
    </script>
@endpush
