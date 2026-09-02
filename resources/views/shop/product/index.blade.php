@extends('layouts.app')
@section('header-title', __('Product List'))

@section('content')
    <div class="container-fluid px-4 py-3 mb-5 pb-5">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0 fw-bold text-dark" style="font-size: 22px; letter-spacing: -0.3px;">
                    {{ __('Products List') }}
                </h3>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 12px;">
                    {{ $products->total() }} {{ __('Total') }}
                </span>
            </div>
        </div>

        <!-- Flash Deal Alert -->
        @if ($flashSale)
            <div class="mb-4">
                <div class="alert flash-deal-alert d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex flex-column">
                        <div class="deal-text">{{ $flashSale->name }}</div>
                        <div class="deal-title">{{ __('Coming Soon') }}</div>
                    </div>
                    <div class="countdown d-flex align-items-center">
                        <div class="countdown-section"><div class="countdown-label">{{ __('Days') }}</div><div id="days" class="countdown-time">00</div></div>
                        <div class="countdown-section"><div class="countdown-label">{{ __('Hours') }}</div><div id="hours" class="countdown-time">00</div></div>
                        <div class="countdown-section"><div class="countdown-label">{{ __('Minutes') }}</div><div id="minutes" class="countdown-time">00</div></div>
                        <div class="countdown-section"><div class="countdown-label">{{ __('Seconds') }}</div><div id="seconds" class="countdown-time">00</div></div>
                    </div>
                    @hasPermission('shop.flashSale.show')
                        <a href="{{ route('shop.flashSale.show', $flashSale->id) }}" class="btn btn-primary py-2.5 addBtn">
                            Add Product
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endif

        <!-- Filter Product Modal -->
        <form action="" method="GET">
            <div class="modal fade" id="filterProductModal" tabindex="-1" aria-labelledby="filterProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header border-bottom pb-3">
                            <h5 class="modal-title fw-bold" id="filterProductModalLabel">{{ __('Filter Products') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div>
                                <x-select label="Category" name="category" placeholder="Select Category">
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>

                            <div class="mt-3">
                                <x-select label="Brand" name="brand" placeholder="All Brand">
                                    <option value="">{{ __('All Brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>

                            <div class="mt-3">
                                <x-select label="Color" name="color" placeholder="All Color">
                                    <option value="">{{ __('All Color') }}</option>
                                    @foreach ($colors as $color)
                                        <option value="{{ $color->id }}" {{ request('color') == $color->id ? 'selected' : '' }}>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>
                        <div class="modal-footer border-top d-flex justify-content-between p-3">
                            <a href="{{ route('shop.product.index') }}" class="btn btn-light px-4 py-2 rounded-3 fw-semibold">
                                {{ __('Reset Filters') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">
                                {{ __('Apply Filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Main Product Card & Controls Container -->
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-5">
            <div class="card-body p-4">
                <!-- Management Toolbar Form -->
                <form action="" method="GET" id="productFilterSortForm" class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom flex-wrap">
                    <!-- Left: Search Box -->
                    <div class="input-group input-group-solid rounded-3 overflow-hidden" style="max-width: 320px; border: 1px solid #e2e8f0;">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-white border-0 ps-1 text-dark"
                            placeholder="{{ __('Search product name...') }}" value="{{ request('search') }}" style="font-size: 13.5px;">
                        @if(request('search'))
                            <a href="{{ route('shop.product.index') }}" class="input-group-text bg-white border-0 text-muted text-decoration-none pe-3" title="Clear">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Right Controls Group -->
                    <div class="d-flex align-items-center gap-2.5 flex-wrap ms-auto">
                        @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                        @if(request('brand')) <input type="hidden" name="brand" value="{{ request('brand') }}"> @endif
                        @if(request('color')) <input type="hidden" name="color" value="{{ request('color') }}"> @endif

                        <!-- Sort Select -->
                        <div class="d-flex align-items-center gap-1.5 bg-light rounded-3 px-2 py-1 border border-secondary-subtle">
                            <i class="bi bi-sort-down text-muted ps-1" style="font-size: 14px;"></i>
                            <select name="sort" id="sortSelect" class="form-select form-select-sm bg-transparent border-0 text-dark fw-semibold" style="font-size: 12.5px; width: 145px; cursor: pointer;" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>{{ __('Latest Added') }}</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest Added') }}</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('Name A–Z') }}</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>{{ __('Name Z–A') }}</option>
                                <option value="stock_high" {{ request('sort') == 'stock_high' ? 'selected' : '' }}>{{ __('Stock High-Low') }}</option>
                                <option value="stock_low" {{ request('sort') == 'stock_low' ? 'selected' : '' }}>{{ __('Stock Low-High') }}</option>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3 px-3 py-2 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#filterProductModal">
                            <i class="bi bi-funnel"></i>
                            {{ __('Filter') }}
                        </button>
                    </div>
                </form>

                <!-- TABLE VIEW CONTAINER (DEFAULT VIEW) -->
                <div id="productTableView" class="table-responsive">
                    <table class="table table-sleek align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Product Name') }}</th>
                                <th class="text-center" style="width: 130px;">{{ __('Design / Code') }}</th>
                                <th class="text-center" style="width: 120px;">{{ __('Variants') }}</th>
                                <th class="text-center" style="width: 120px;">{{ __('Stock') }}</th>
                                <th class="text-end" style="width: 120px;">{{ __('Selling Price') }}</th>
                                <th class="text-end" style="width: 110px;">{{ __('MRP') }}</th>
                                <th class="text-center" style="width: 130px;">{{ __('Status') }}</th>
                                <th class="text-center" style="width: 110px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $key => $product)
                                @php
                                    $rawVariants = $product->designMaster?->inwardProductDesign ?? collect([]);
                                    $inwardVariants = $rawVariants->filter(function($iv) {
                                        $hasBarcode = \App\Models\ProductBarcode::where('inward_product_id', $iv->id)->exists();
                                        $isOnline = (bool)($iv->is_online_product ?? false);
                                        return $hasBarcode && $isOnline;
                                    })->values();
                                    $inwardVariantData = $inwardVariants->map(function($iv) {
                                        $colors = $iv->colors ? $iv->colors->pluck('name')->filter()->implode(', ') : '';
                                        $sizes = $iv->sizes ? $iv->sizes->pluck('name')->filter()->implode(', ') : '';
                                        return [
                                            'color' => !empty($colors) ? $colors : 'N/A',
                                            'size' => !empty($sizes) ? $sizes : 'N/A',
                                            'qty' => (int)($iv->quantity ?? $iv->qty ?? 0),
                                            'purc_rate' => (float)($iv->buy_price ?? $iv->net_purc_rate ?? 0),
                                            'disc' => (float)($iv->discount_price ?? 0),
                                            'mrp' => (float)($iv->mrp ?? $iv->price ?? 0),
                                            'price' => (float)($iv->price ?? $iv->mrp ?? 0),
                                        ];
                                    });

                                    // Stock calculated from total quantity of all variants
                                    $displayStock = ($inwardVariants->count() > 0)
                                        ? $inwardVariantData->sum('qty')
                                        : ($product->quantity ?? 0);

                                    // Selling Price of the first variant
                                    $firstVariant = $inwardVariants->first();
                                    $displayPrice = ($firstVariant && !empty($firstVariant->price))
                                        ? $firstVariant->price
                                        : (($firstVariant && !empty($firstVariant->mrp)) ? $firstVariant->mrp : $product->price);

                                    $displayMrp = ($firstVariant && !empty($firstVariant->mrp))
                                        ? $firstVariant->mrp
                                        : $product->mrp;

                                    $variantCount = $inwardVariantData->count();
                                    if ($variantCount == 0) {
                                        $variantCount = max(1, count($product->colors) * count($product->sizes));
                                        if ($product->barcodes && $product->barcodes->count() > 0) {
                                            $variantCount = max($variantCount, $product->barcodes->count());
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center text-muted font-monospace fw-semibold" style="font-size: 12.5px;">
                                        {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="product-img-thumb flex-shrink-0" loading="lazy" />
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 280px; font-size: 13.5px;" title="{{ $product->name }}">
                                                    {{ $product->name }}
                                                </div>
                                                <div class="text-muted d-block" style="font-size: 11.5px;">
                                                    Added: {{ $product->created_at ? $product->created_at->format('d M, Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-monospace fw-semibold text-secondary" style="font-size: 12.5px;">
                                            {{ $product->designMaster->design_number ?? $product->code ?? $product->sku ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 text-nowrap fw-semibold btn-view-variants"
                                            style="font-size: 11px;"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#productVariantOffcanvas"
                                            data-product-name="{{ $product->name }}"
                                            data-product-thumbnail="{{ $product->thumbnail }}"
                                            data-design-number="{{ $product->designMaster?->design_number ?? $product->code ?? '—' }}"
                                            data-price="{{ showCurrency($displayPrice) }}"
                                            data-mrp="{{ showCurrency($displayMrp ?? $displayPrice) }}"
                                            data-inward-variants='@json($inwardVariantData)'
                                            data-colors='@json($product->colors)'
                                            data-sizes='@json($product->sizes)'
                                            data-barcodes='@json($product->barcodes)'>
                                            <i class="bi bi-layers me-1"></i>{{ $variantCount }} {{ __('Var') }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        @if($displayStock <= 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 11px;">
                                                {{ __('Out of Stock') }}
                                            </span>
                                        @elseif($displayStock < 25)
                                            <div class="font-monospace fw-bold text-dark mb-0" style="font-size: 13px;">{{ $displayStock }}</div>
                                            <span class="stock-badge-low" style="font-size: 10.5px;">{{ __('Low Stock') }}</span>
                                        @else
                                            <span class="font-monospace fw-bold text-dark" style="font-size: 13px;">{{ $displayStock }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-primary" style="font-size: 14px;">
                                        {{ showCurrency($displayPrice) }}
                                    </td>
                                    <td class="text-end font-monospace text-muted" style="font-size: 13px;">
                                        {{ showCurrency($displayMrp ?? $displayPrice) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            @if($product->is_active)
                                                <span class="badge bg-success-subtle text-success border-0 px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                    {{ __('Published') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border-0 px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif

                                            <label class="switch mb-0" data-bs-toggle="tooltip" data-bs-title="{{ __('Toggle Status') }}">
                                                <a href="{{ route('shop.product.toggle', $product->id) }}">
                                                    <input type="checkbox" {{ $product->is_active ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </a>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1.5 justify-content-center">
                                            @hasPermission('shop.product.show')
                                                <a href="{{ route('shop.product.show', $product->id) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-title="{{ __('View Product') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endhasPermission
                                            @hasPermission('shop.product.edit')
                                                <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-sm btn-icon btn-light text-secondary rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-title="{{ __('Edit Product') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endhasPermission
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                        {{ __('No Products Found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination & Results Count -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4 pt-3 border-top">
                    <div class="text-muted small font-monospace">
                        Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    <!-- Right Side Offcanvas Drawer for Product Variants Detail -->
    <div class="offcanvas offcanvas-end rounded-start-4 border-0 shadow-lg" tabindex="-1" id="productVariantOffcanvas" aria-labelledby="productVariantOffcanvasLabel" style="width: 540px;">
        <div class="offcanvas-header border-bottom p-3">
            <h5 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="productVariantOffcanvasLabel">
                <i class="bi bi-layers text-primary"></i>
                {{ __('Inward Product – Color & Size Wise Details') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4">
            <!-- Variants Breakdown List -->
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size: 14.5px;">
                <i class="bi bi-list-stars text-primary fs-5" style="margin-right: 8px !important;"></i>
                <span>{{ __('Inward Variants List') }}</span>
            </h6>
            <div id="offcanvasVariantsContainer" class="w-100">
                <!-- Dynamically rendered -->
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // Variant Drawer Event Handler
        $(document).on('click', '.btn-view-variants', function() {
            const btn = $(this);
            const inwardVariants = btn.data('inward-variants') || [];
            const colors = btn.data('colors') || [];
            const sizes = btn.data('sizes') || [];
            const barcodes = btn.data('barcodes') || [];

            if (inwardVariants && inwardVariants.length > 0) {
                let totalQty = 0;
                let totalPurc = 0;
                let totalMrp = 0;

                let html = '<div class="table-responsive border rounded-3 shadow-2xs mb-0" style="overflow-x: auto; max-height: calc(100vh - 240px); overflow-y: auto;">' +
                    '<table class="table table-hover align-middle mb-0" style="font-size: 12.5px; min-width: 480px;">' +
                    '<thead class="table-light position-sticky top-0" style="z-index: 1;">' +
                        '<tr>' +
                            '<th class="text-center py-2.5" style="width: 40px;">SL</th>' +
                            '<th class="py-2.5">Color</th>' +
                            '<th class="text-center py-2.5">Size</th>' +
                            '<th class="text-center py-2.5">Qty</th>' +
                            '<th class="text-end py-2.5">Purc Rate</th>' +
                            '<th class="text-center py-2.5">Disc</th>' +
                            '<th class="text-end py-2.5">MRP</th>' +
                        '</tr>' +
                    '</thead><tbody>';

                inwardVariants.forEach((iv, idx) => {
                    totalQty += parseInt(iv.qty || 0);
                    totalPurc += parseFloat(iv.purc_rate || 0) * parseInt(iv.qty || 0);
                    totalMrp += parseFloat(iv.mrp || 0);

                    // Clean text for Color & Size without background pills as requested
                    const colorText = (iv.color && iv.color !== 'N/A')
                        ? `<span class="fw-semibold text-dark">${iv.color}</span>`
                        : `<span class="text-muted small">N/A</span>`;

                    const sizeText = (iv.size && iv.size !== 'N/A')
                        ? `<span class="fw-semibold font-monospace text-dark">${iv.size}</span>`
                        : `<span class="text-muted small">N/A</span>`;

                    const discText = iv.disc > 0
                        ? `<span class="badge bg-danger-subtle text-danger border-0 px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10.5px;">${iv.disc}%</span>`
                        : `<span class="text-muted small">0%</span>`;

                    const purcRateText = parseFloat(iv.purc_rate || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const mrpText = parseFloat(iv.mrp || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    html += `<tr>
                        <td class="text-center text-secondary fw-semibold">${idx + 1}</td>
                        <td>${colorText}</td>
                        <td class="text-center">${sizeText}</td>
                        <td class="text-center font-monospace fw-bold text-dark">${iv.qty}</td>
                        <td class="text-end font-monospace text-primary fw-semibold">₹${purcRateText}</td>
                        <td class="text-center">${discText}</td>
                        <td class="text-end font-monospace text-success fw-bold">₹${mrpText}</td>
                    </tr>`;
                });

                const totalMrpText = totalMrp.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                html += `<tr class="table-light fw-bold">
                    <td colspan="3" class="text-end pe-2 text-dark">Total:</td>
                    <td class="text-center font-monospace text-dark fs-6">${totalQty}</td>
                    <td class="text-end font-monospace text-muted">—</td>
                    <td></td>
                    <td class="text-end font-monospace text-success fs-6">₹${totalMrpText}</td>
                </tr>`;

                html += '</tbody></table></div>';
                $('#offcanvasVariantsContainer').html(html);
                return;
            }

            let html = '<div class="table-responsive border rounded-3 shadow-2xs"><table class="table table-hover align-middle mb-0" style="font-size: 12.5px;"><thead class="table-light"><tr><th>Color / Spec</th><th class="text-center">Size / Code</th><th class="text-end">Price / Status</th></tr></thead><tbody>';

            if (barcodes.length > 0) {
                barcodes.forEach((b) => {
                    const colorHtml = b.color && b.color.name
                        ? `<span class="fw-semibold text-dark">${b.color.name}</span>`
                        : `<span class="badge bg-info-subtle text-info border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Color: N/A</span>`;
                    const sizeHtml = b.size && b.size.name
                        ? `<span class="fw-semibold font-monospace text-dark">${b.size.name}</span>`
                        : `<span class="badge bg-success-subtle text-success border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Size: N/A</span>`;
                    const statusBadge = b.is_sold == 1
                        ? '<span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-0.5" style="font-size: 10px;">Sold</span>'
                        : '<span class="badge bg-success-subtle text-success border-0 px-2 py-0.5" style="font-size: 10px;">Available</span>';
                    html += `<tr>
                        <td>
                            <div>${colorHtml}</div>
                            <small class="text-muted font-monospace" style="font-size: 10.5px;">${b.barcode_number || 'N/A'}</small>
                        </td>
                        <td class="text-center">${sizeHtml}</td>
                        <td class="text-end">${statusBadge}</td>
                    </tr>`;
                });
            } else if (colors.length > 0 || sizes.length > 0) {
                if (colors.length > 0 && sizes.length > 0) {
                    colors.forEach(c => {
                        sizes.forEach(s => {
                            const cHtml = c.name && c.name !== 'N/A'
                                ? `<span class="d-inline-block rounded-circle me-1 border" style="width: 10px; height: 10px; background-color: ${c.color_code || '#cbd5e1'};"></span><span class="fw-semibold text-dark">${c.name}</span>`
                                : `<span class="badge bg-info-subtle text-info border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Color: N/A</span>`;
                            const sHtml = s.name && s.name !== 'N/A'
                                ? `<span class="fw-semibold font-monospace text-dark">${s.name}</span>`
                                : `<span class="badge bg-success-subtle text-success border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Size: N/A</span>`;
                            html += `<tr>
                                <td>${cHtml}</td>
                                <td class="text-center">${sHtml}</td>
                                <td class="text-end font-monospace text-primary fw-bold">${price}</td>
                            </tr>`;
                        });
                    });
                } else if (colors.length > 0) {
                    colors.forEach(c => {
                        const cHtml = c.name && c.name !== 'N/A'
                            ? `<span class="d-inline-block rounded-circle me-1 border" style="width: 10px; height: 10px; background-color: ${c.color_code || '#cbd5e1'};"></span><span class="fw-semibold text-dark">${c.name}</span>`
                            : `<span class="badge bg-info-subtle text-info border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Color: N/A</span>`;
                        html += `<tr>
                            <td>${cHtml}</td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Size: N/A</span></td>
                            <td class="text-end font-monospace text-primary fw-bold">${price}</td>
                        </tr>`;
                    });
                } else {
                    sizes.forEach(s => {
                        const sHtml = s.name && s.name !== 'N/A'
                            ? `<span class="fw-semibold font-monospace text-dark">${s.name}</span>`
                            : `<span class="badge bg-success-subtle text-success border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Size: N/A</span>`;
                        html += `<tr>
                            <td><span class="badge bg-info-subtle text-info border-0 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 10px;">Color: N/A</span></td>
                            <td class="text-center">${sHtml}</td>
                            <td class="text-end font-monospace text-primary fw-bold">${price}</td>
                        </tr>`;
                    });
                }
            } else {
                html += `<tr>
                    <td><span class="badge bg-info-subtle text-info border-0 px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">Color: N/A</span></td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success border-0 px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">Size: N/A</span></td>
                    <td class="text-end font-monospace text-primary fw-bold" style="font-size: 13.5px;">${price}</td>
                </tr>`;
            }

            html += '</tbody></table></div>';
            html += '<div class="p-2.5 bg-light rounded-3 mt-3 text-muted border small d-flex align-items-center gap-2" style="font-size: 11.5px;"><i class="bi bi-info-circle text-primary fs-6"></i> Standard single product entry (Color: N/A, Size: N/A).</div>';
            $('#offcanvasVariantsContainer').html(html);
        });

        $(".confirmApprove").on("click", function(e) {
            e.preventDefault();
            const url = $(this).attr("href");
            Swal.fire({
                title: "Are you sure?",
                text: "You want to approve this product",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Approve it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    </script>

    @if ($flashSale)
        <script>
            // Set the start and end date/time
            var startDateAndTime = "{{ $flashSale->start_date }}T{{ $flashSale->start_time }}";
            var endDateAndTime = "{{ $flashSale->end_date }}T{{ $flashSale->end_time }}";
            let startDate = new Date(startDateAndTime).getTime();
            let endDate = new Date(endDateAndTime).getTime();

            // Update the countdown every 1 second
            let countdownInterval = setInterval(() => {
                let now = new Date().getTime();

                // If current time is before the start date, show "Deal Coming" message
                if (now < startDate) {
                    let distanceToStart = startDate - now;

                    // Time calculations for days, hours, minutes, and seconds
                    let days = Math.floor(distanceToStart / (1000 * 60 * 60 * 24));
                    let hours = Math.floor((distanceToStart % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutes = Math.floor((distanceToStart % (1000 * 60 * 60)) / (1000 * 60));
                    let seconds = Math.floor((distanceToStart % (1000 * 60)) / 1000);

                    // Display the countdown with a "Deal Coming" message
                    document.getElementById("days").innerHTML = String(days).padStart(2, '0');
                    document.getElementById("hours").innerHTML = String(hours).padStart(2, '0');
                    document.getElementById("minutes").innerHTML = String(minutes).padStart(2, '0');
                    document.getElementById("seconds").innerHTML = String(seconds).padStart(2, '0');
                    return;
                }

                // Once the current time is after the start date and before the end date, show the active countdown
                let distance = endDate - now;

                // If the deal has ended, stop the countdown and show the message
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    document.getElementById("days").innerHTML = "00";
                    document.getElementById("hours").innerHTML = "00";
                    document.getElementById("minutes").innerHTML = "00";
                    document.getElementById("seconds").innerHTML = "00";
                    document.querySelector(".deal-text").innerHTML = "Deal Ended!";
                    return;
                }

                // Time calculations for days, hours, minutes, and seconds
                let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result
                document.getElementById("days").innerHTML = String(days).padStart(2, '0');
                document.getElementById("hours").innerHTML = String(hours).padStart(2, '0');
                document.getElementById("minutes").innerHTML = String(minutes).padStart(2, '0');
                document.getElementById("seconds").innerHTML = String(seconds).padStart(2, '0');
            }, 1000);
        </script>
    @endif
@endpush
@push('css')
    <style>
        /* Flash Deal Alert Styles */
        .flash-deal-alert {
            background: url("{{ asset('assets/images/flash-sale.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 8px;
            color: white;
            border-radius: 8px;
            padding: 16px 32px;
        }

        .deal-title,
        .deal-text {
            font-size: 24px;
            font-weight: 600;
            color: white;
            margin: 0;
            line-height: 32px;
        }

        /* Countdown Timer Styles */
        .countdown {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .countdown-section {
            text-align: center;
            padding: 4px 8px;
            border-radius: 8px;
            background-color: white;
            min-width: 68px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .countdown-label {
            font-size: 12px;
            color: #000;
        }

        .countdown-time {
            font-size: 20px;
            font-weight: bold;
            color: var(--theme-color);
        }

        .addBtn {
            border-radius: 25px;
            padding: 10px 20px;
        }

        /* Sleek Modern Admin Table (Matching Image 2 Reference Design) */
        .table-sleek {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100%;
        }

        .table-sleek thead th {
            background-color: #f8fafc !important;
            color: #64748b !important;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px !important;
            border-top: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .table-sleek thead th:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .table-sleek thead th:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .table-sleek tbody td {
            padding: 14px 16px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            color: #1e293b;
            font-size: 13.5px;
            vertical-align: middle;
        }

        .table-sleek tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        .product-img-thumb {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .stock-badge-low {
            color: #d97706;
            font-weight: 600;
        }

        .btn-white {
            background-color: #ffffff !important;
            color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Ensure Offcanvas Variants Table renders all rows cleanly without clipping, opacity reduction, or hidden overflow */
        #offcanvasVariantsContainer {
            width: 100% !important;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        #offcanvasVariantsContainer .table-responsive {
            display: block !important;
            width: 100% !important;
            opacity: 1 !important;
            visibility: visible !important;
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
        }

        #offcanvasVariantsContainer table {
            display: table !important;
            width: 100% !important;
            opacity: 1 !important;
            visibility: visible !important;
            height: auto !important;
            max-height: none !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }

        #offcanvasVariantsContainer table thead {
            display: table-header-group !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        #offcanvasVariantsContainer table tbody {
            display: table-row-group !important;
            opacity: 1 !important;
            visibility: visible !important;
            height: auto !important;
            max-height: none !important;
        }

        #offcanvasVariantsContainer table tr,
        #offcanvasVariantsContainer table tbody tr {
            display: table-row !important;
            opacity: 1 !important;
            visibility: visible !important;
            height: auto !important;
            max-height: none !important;
            transform: none !important;
            transition: none !important;
        }

        #offcanvasVariantsContainer table td,
        #offcanvasVariantsContainer table th {
            display: table-cell !important;
            opacity: 1 !important;
            visibility: visible !important;
            height: auto !important;
            vertical-align: middle !important;
        }
    </style>
@endpush
