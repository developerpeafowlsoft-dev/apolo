@php
    use App\Enums\OrderStatus;
    $orderStatuses = OrderStatus::cases();
@endphp

<!--- SECTION 1: OVERVIEW & MESSAGES --->
<li class="menu-divider">
    <span class="menu-title">{{ __('Overview & Messages') }}</span>
</li>
<li>
    <a class="menu {{ $request->routeIs('shop.dashboard.*') ? 'active' : '' }}"
        href="{{ route('shop.dashboard.index') }}">
        <span>
            <img class="menu-icon" src="{{ asset('assets/icons-admin/dashboard.svg') }}" alt="icon" loading="lazy" />
            {{ __('Dashboard') }}
        </span>
    </a>
</li>

<li>
    <a class="menu {{ $request->routeIs('shop.customer.chat.index') ? 'active' : '' }}"
        href="{{ route('shop.customer.chat.index') }}">
        <span class="position-relative">
            <img class="menu-icon" src="{{ asset('assets/icons-admin/message.svg') }}" alt="icon" loading="lazy" />
            {{ __('Messages') }}
            <span id="unread-message-badge" class="bg-success text-white ms-2 position-absolute d-none"
                style="top: 0; transform: translateY(-50%); left: 5px; height: 16px; width: 16px; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center;">
                0
            </span>
        </span>
    </a>
</li>

@if ($generaleSetting?->business_based_on == 'subscription')
    @hasPermission('shop.subscription.index')
        <li>
            <a href="{{ route('shop.subscription.index') }}"
                class="menu {{ request()->routeIs('shop.subscription.*') ? 'active' : '' }}">
                <span>
                    <img class="menu-icon" src="{{ asset('assets/icons-admin/crown.svg') }}" alt="icon" loading="lazy" />
                    {{ __('Subscription') }}
                </span>
            </a>
        </li>
    @endhasPermission
@endif


<!--- SECTION 2: SALES & POS OPERATIONS --->
@hasPermission(['shop.pos.index', 'shop.pos.draft', 'shop.pos.sales', 'shop.order.index'])
    <li class="menu-divider">
        <span class="menu-title">{{ __('Sales & POS Operations') }}</span>
    </li>
@endhasPermission

@hasPermission('shop.pos.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.pos.index') ? 'active' : '' }}" href="{{ route('shop.pos.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/pos.svg') }}" alt="icon" loading="lazy" />
                {{ __('POS Terminal') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.pos.sales')
    <li>
        <a class="menu {{ $request->routeIs('shop.pos.sales') ? 'active' : '' }}" href="{{ route('shop.pos.sales') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/pos-sale.svg') }}" alt="icon" loading="lazy" />
                {{ __('POS Sales') }}
            </span>
        </a>
    </li>
    <li>
        <a class="menu {{ (request()->routeIs('shop.pos.history.index') && request()->get('tab') === 'returns') ? 'active' : '' }}" href="{{ route('shop.pos.history.index') }}?tab=returns">
            <span>
                <i class="fa-solid fa-rotate-left menu-icon text-muted" style="font-size: 16px; margin-right: 12px; width: 18px; text-align: center; display: inline-block;"></i>
                {{ __('POS Returns') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.pos.draft')
    <li>
        <a class="menu {{ $request->routeIs('shop.pos.draft') ? 'active' : '' }}" href="{{ route('shop.pos.draft') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/draft.svg') }}" alt="icon" loading="lazy" />
                {{ __('POS Drafts') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.order.index')
    <li>
        <a class="menu {{ request()->routeIs('shop.order.*') ? 'active' : '' }}" data-bs-toggle="collapse"
            href="#orderMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/orders.svg') }}" alt="icon" loading="lazy" />
                {{ __('All Orders') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ $request->routeIs('shop.order.*') ? 'show' : '' }}" id="orderMenu">
            <div class="listBar">
                <a href="{{ route('shop.order.index') }}"
                    class="subMenu hasCount {{ request()->url() === route('shop.order.index') ? 'active' : '' }}">
                    {{ __('All') }} <span class="count statusAll">{{ $allOrders > 99 ? '99+' : $allOrders }}</span>
                </a>
                @foreach ($orderStatuses as $status)
                    <a href="{{ route('shop.order.index', str_replace(' ', '_', $status->value)) }}"
                        class="subMenu hasCount {{ request()->url() === route('shop.order.index', str_replace(' ', '_', $status->value)) ? 'active' : '' }}">
                        <span>{{ __($status->value) }}</span>
                        <span class="count status{{ Str::camel($status->value) }}">
                            {{ ${Str::camel($status->value)} > 99 ? '99+' : ${Str::camel($status->value)} }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </li>
@endhasPermission


<!--- SECTION 3: CATALOG & INVENTORY --->
@hasPermission(['shop.product.index', 'shop.inwardProduct.index', 'shop.purchaseProduct.index', 'shop.category.index', 'shop.subcategory.index', 'admin.flashSale.index', 'shop.brand.index', 'shop.color.index', 'shop.size.index', 'shop.unit.index', 'shop.material.index', 'shop.itemMaster.index', 'shop.designMaster.index'])
    <li class="menu-divider">
        <span class="menu-title">{{ __('Catalog & Inventory') }}</span>
    </li>
@endhasPermission

@hasPermission('shop.product.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.product.*') ? 'active' : '' }}" href="{{ route('shop.product.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/product.svg') }}" alt="icon" loading="lazy" />
                {{ __('Products') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.inwardProduct.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.inwardProduct.*') ? 'active' : '' }}" href="{{ route('shop.inwardProduct.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/inward-product.svg') }}" alt="icon" loading="lazy" />
                {{ __('Inward Invoices') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.openingStock.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.openingStock.*') ? 'active' : '' }}" href="{{ route('shop.openingStock.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/inward-product.svg') }}" alt="icon" loading="lazy" />
                {{ __('Opening Stock') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.purchaseProduct.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.purchaseProduct.*') ? 'active' : '' }}" href="{{ route('shop.purchaseProduct.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/purchase-product.svg') }}" alt="icon" loading="lazy" />
                {{ __('Purchases') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission('shop.supplierDuePayment.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.supplierDuePayment.*') ? 'active' : '' }}" href="{{ route('shop.supplierDuePayment.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/account-balance.svg') }}" alt="icon" loading="lazy" />
                {{ __('Supplier Credit Dues') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission(['shop.category.index', 'shop.subcategory.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.category.*', 'shop.subcategory.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#categoryMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/category.svg') }}" alt="icon" loading="lazy" />
                {{ __('Categories') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ $request->routeIs('shop.category.*', 'shop.subcategory.*') ? 'show' : '' }}" id="categoryMenu">
            <div class="listBar">
                @hasPermission('shop.category.index')
                    <a href="{{ route('shop.category.index') }}"
                        class="subMenu {{ request()->routeIs('shop.category.*') ? 'active' : '' }}">
                        {{ __('Category') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.subcategory.index')
                    <a href="{{ route('shop.subcategory.index') }}"
                        class="subMenu {{ request()->routeIs('shop.subcategory.*') ? 'active' : '' }}">
                        {{ __('Sub Category') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission

@hasPermission('admin.flashSale.index')
    <li>
        <a href="{{ route('shop.flashSale.index') }}"
            class="menu {{ request()->routeIs('shop.flashSale.*') ? 'active' : '' }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/flash.svg') }}" alt="icon" loading="lazy" />
                {{ __('Flash Sales') }}
            </span>
        </a>
    </li>
@endhasPermission

@hasPermission(['shop.itemMaster.index', 'shop.designMaster.index', 'shop.brand.index', 'shop.color.index', 'shop.size.index', 'shop.unit.index', 'shop.material.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.itemMaster.*', 'shop.designMaster.*', 'shop.brand.*', 'shop.color.*', 'shop.size.*', 'shop.unit.*', 'shop.material.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#catalogMastersMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/boxes.svg') }}" alt="icon" loading="lazy" />
                {{ __('Catalog Masters') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.itemMaster.*', 'shop.designMaster.*', 'shop.brand.*', 'shop.color.*', 'shop.size.*', 'shop.unit.*', 'shop.material.*') ? 'show' : '' }}" id="catalogMastersMenu">
            <div class="listBar">
                @hasPermission('shop.itemMaster.index')
                    <a href="{{ route('shop.itemMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.itemMaster.*') ? 'active' : '' }}">
                        {{ __('Item Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.designMaster.index')
                    <a href="{{ route('shop.designMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.designMaster.*') ? 'active' : '' }}">
                        {{ __('Design Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.brand.index')
                    <a href="{{ route('shop.brand.index') }}"
                        class="subMenu {{ request()->routeIs('shop.brand.*') ? 'active' : '' }}">
                        {{ __('Brand Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.color.index')
                    <a href="{{ route('shop.color.index') }}"
                        class="subMenu {{ request()->routeIs('shop.color.*') ? 'active' : '' }}">
                        {{ __('Color Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.size.index')
                    <a href="{{ route('shop.size.index') }}"
                        class="subMenu {{ request()->routeIs('shop.size.*') ? 'active' : '' }}">
                        {{ __('Sizes Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.unit.index')
                    <a href="{{ route('shop.unit.index') }}"
                        class="subMenu {{ request()->routeIs('shop.unit.*') ? 'active' : '' }}">
                        {{ __('Unit Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.material.index')
                    <a href="{{ route('shop.material.index') }}"
                        class="subMenu {{ request()->routeIs('shop.material.*') ? 'active' : '' }}">
                        {{ __('Material Master') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission


<!--- SECTION 4: ACCOUNTING & FINANCE --->
@hasPermission(['shop.accountMaster.index', 'shop.accountBalance.index', 'shop.bankMaster.index', 'shop.counterMaster.index', 'shop.tdsMaster.index', 'shop.hsnMaster.index', 'shop.withdraw.index'])
    <li class="menu-divider">
        <span class="menu-title">{{ __('Accounting & Finance') }}</span>
    </li>
@endhasPermission

<li>
    <a class="menu {{ request()->routeIs('shop.reports.accountingDashboard') ? 'active' : '' }}"
        href="{{ route('shop.reports.accountingDashboard') }}">
        <span>
            <img class="menu-icon" src="{{ asset('assets/icons-admin/dashboard.svg') }}" alt="icon" loading="lazy" />
            {{ __('Accounting Hub') }}
        </span>
    </a>
</li>

<li>
    <a class="menu {{ request()->routeIs('shop.reports.vouchers', 'shop.reports.generalLedger', 'shop.reports.financialStatements', 'shop.reports.purchaseReturns', 'shop.reports.supplierPayments', 'shop.supplierDuePayment.*', 'shop.reports.codReconciliation', 'shop.reports.inventoryValuation') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#accountingBooksMenu">
        <span>
            <img class="menu-icon" src="{{ asset('assets/icons-admin/accounting.svg') }}" alt="icon" loading="lazy" />
            {{ __('Books & Vouchers') }}
        </span>
        <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
    </a>
    <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.reports.vouchers', 'shop.reports.generalLedger', 'shop.reports.financialStatements', 'shop.reports.purchaseReturns', 'shop.reports.supplierPayments', 'shop.supplierDuePayment.*', 'shop.reports.codReconciliation', 'shop.reports.inventoryValuation') ? 'show' : '' }}" id="accountingBooksMenu">
        <div class="listBar">
            <a href="{{ route('shop.reports.vouchers') }}"
                class="subMenu {{ request()->routeIs('shop.reports.vouchers') ? 'active' : '' }}">
                {{ __('General Journal & Vouchers') }}
            </a>
            <a href="{{ route('shop.reports.generalLedger') }}"
                class="subMenu {{ request()->routeIs('shop.reports.generalLedger') ? 'active' : '' }}">
                {{ __('General Ledger Statement') }}
            </a>
            <a href="{{ route('shop.reports.financialStatements') }}"
                class="subMenu {{ request()->routeIs('shop.reports.financialStatements') ? 'active' : '' }}">
                {{ __('Trial Balance, P&L, Balance Sheet') }}
            </a>
            <a href="{{ route('shop.reports.purchaseReturns') }}"
                class="subMenu {{ request()->routeIs('shop.reports.purchaseReturns') ? 'active' : '' }}">
                {{ __('Purchase Returns & Debit Notes') }}
            </a>
            <a href="{{ route('shop.reports.supplierPayments') }}"
                class="subMenu {{ request()->routeIs('shop.reports.supplierPayments') ? 'active' : '' }}">
                {{ __('Supplier Bill Settlements') }}
            </a>
            <a href="{{ route('shop.supplierDuePayment.index') }}"
                class="subMenu {{ request()->routeIs('shop.supplierDuePayment.*') ? 'active' : '' }}">
                {{ __('Supplier Credit Dues (Credit Days)') }}
            </a>
            <a href="{{ route('shop.reports.codReconciliation') }}"
                class="subMenu {{ request()->routeIs('shop.reports.codReconciliation') ? 'active' : '' }}">
                {{ __('COD Remittance & Settlement') }}
            </a>
            <a href="{{ route('shop.reports.inventoryValuation') }}"
                class="subMenu {{ request()->routeIs('shop.reports.inventoryValuation') ? 'active' : '' }}">
                {{ __('Closing Inventory Valuation') }}
            </a>
        </div>
    </div>
</li>

@hasPermission(['shop.accountMaster.index', 'shop.accountBalance.index', 'shop.bankMaster.index', 'shop.counterMaster.index', 'shop.tdsMaster.index', 'shop.hsnMaster.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.accountMaster.*', 'shop.accountBalance.*', 'shop.bankMaster.*', 'shop.counterMaster.*', 'shop.tdsMaster.*', 'shop.hsnMaster.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#accountingMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/account-balance.svg') }}" alt="icon" loading="lazy" />
                {{ __('Ledgers & Masters') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.accountMaster.*', 'shop.accountBalance.*', 'shop.bankMaster.*', 'shop.counterMaster.*', 'shop.tdsMaster.*', 'shop.hsnMaster.*') ? 'show' : '' }}" id="accountingMenu">
            <div class="listBar">
                @hasPermission('shop.accountMaster.index')
                    <a href="{{ route('shop.accountMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.accountMaster.*') ? 'active' : '' }}">
                        {{ __('Account Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.accountBalance.index')
                    <a href="{{ route('shop.accountBalance.index') }}"
                        class="subMenu {{ request()->routeIs('shop.accountBalance.*') ? 'active' : '' }}">
                        {{ __('Account Balance') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.bankMaster.index')
                    <a href="{{ route('shop.bankMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.bankMaster.*') ? 'active' : '' }}">
                        {{ __('Bank Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.counterMaster.index')
                    <a href="{{ route('shop.counterMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.counterMaster.*') ? 'active' : '' }}">
                        {{ __('Counter Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.tdsMaster.index')
                    <a href="{{ route('shop.tdsMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.tdsMaster.*') ? 'active' : '' }}">
                        {{ __('TDS Master') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.hsnMaster.index')
                    <a href="{{ route('shop.hsnMaster.index') }}"
                        class="subMenu {{ request()->routeIs('shop.hsnMaster.*') ? 'active' : '' }}">
                        {{ __('HSN Master') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission

@if (!auth()->user()->hasRole('root'))
    @hasPermission('shop.withdraw.index')
        <li>
            <a class="menu {{ $request->routeIs('shop.withdraw.*') ? 'active' : '' }}"
                href="{{ route('shop.withdraw.index') }}">
                <span>
                    <img class="menu-icon" src="{{ asset('assets/icons-admin/withdraw.svg') }}" alt="icon" loading="lazy" />
                    {{ __('Withdraws') }}
                </span>
            </a>
        </li>
    @endhasPermission
@endif


<!--- SECTION 5: REPORTS & ANALYTICS --->
<li class="menu-divider">
    <span class="menu-title">{{ __('Reports & Analytics') }}</span>
</li>
<li>
    <a class="menu {{ request()->routeIs('shop.reports.*') ? 'active' : '' }}"
        data-bs-toggle="collapse" href="#reportsMenu">
        <span>
            <img class="menu-icon" src="{{ asset('assets/icons-admin/promotional.svg') }}" alt="icon" loading="lazy" />
            {{ __('Reports & Analytics') }}
        </span>
        <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
    </a>
    <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.reports.*') ? 'show' : '' }}" id="reportsMenu">
        <div class="listBar">
            <a href="{{ route('shop.reports.gstDashboard') }}"
                class="subMenu {{ request()->routeIs('shop.reports.gstDashboard') ? 'active' : '' }}">
                {{ __('GST Statutory Report') }}
            </a>
            <a href="{{ route('shop.reports.financialStatements') }}"
                class="subMenu {{ request()->routeIs('shop.reports.financialStatements') ? 'active' : '' }}">
                {{ __('Financial Statements') }}
            </a>
            <a href="{{ route('shop.reports.bankReconciliation') }}"
                class="subMenu {{ request()->routeIs('shop.reports.bankReconciliation') ? 'active' : '' }}">
                {{ __('Cash & Bank BRS') }}
            </a>
            <a href="{{ route('shop.reports.outstandingAgeing') }}"
                class="subMenu {{ request()->routeIs('shop.reports.outstandingAgeing') ? 'active' : '' }}">
                {{ __('Outstanding Ageing') }}
            </a>
            <a href="{{ route('shop.reports.visualAnalytics') }}"
                class="subMenu {{ request()->routeIs('shop.reports.visualAnalytics') ? 'active' : '' }}">
                {{ __('Visual Analytics & Sales') }}
            </a>
            <a href="{{ route('shop.reports.counterProductivity') }}"
                class="subMenu {{ request()->routeIs('shop.reports.counterProductivity') ? 'active' : '' }}">
                {{ __('Counter Productivity') }}
            </a>
        </div>
    </div>
</li>


<!--- SECTION 6: STORE MANAGEMENT & UTILITIES --->
@hasPermission(['shop.profile.index', 'shop.employee.index', 'shop.salesman.index', 'shop.voucher.index', 'shop.banner.index', 'shop.bulk-product-import.index', 'shop.bulk-product-export.index', 'shop.gallery.index'])
    <li class="menu-divider">
        <span class="menu-title">{{ __('Store Settings & Utilities') }}</span>
    </li>
@endhasPermission

@hasPermission(['shop.employee.index', 'shop.salesman.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.employee.*', 'shop.salesman.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#staffMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/employee.svg') }}" alt="icon" loading="lazy" />
                {{ __('Staff & Salesmen') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.employee.*', 'shop.salesman.*') ? 'show' : '' }}" id="staffMenu">
            <div class="listBar">
                @hasPermission('shop.employee.index')
                    <a href="{{ route('shop.employee.index') }}"
                        class="subMenu {{ request()->routeIs('shop.employee.*') ? 'active' : '' }}">
                        {{ __('Employees') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.salesman.index')
                    <a href="{{ route('shop.salesman.index') }}"
                        class="subMenu {{ request()->routeIs('shop.salesman.*') ? 'active' : '' }}">
                        {{ __('Salesmen') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission

@hasPermission(['shop.voucher.index', 'shop.banner.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.voucher.*', 'shop.banner.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#marketingMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/coupon-percent.svg') }}" alt="icon" loading="lazy" />
                {{ __('Marketing & Promos') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.voucher.*', 'shop.banner.*') ? 'show' : '' }}" id="marketingMenu">
            <div class="listBar">
                @hasPermission('shop.voucher.index')
                    <a href="{{ route('shop.voucher.index') }}"
                        class="subMenu {{ request()->routeIs('shop.voucher.*') ? 'active' : '' }}">
                        {{ __('Promo Codes') }}
                    </a>
                @endhasPermission
                @if ($businessModel == 'multi')
                    @hasPermission('shop.banner.index')
                        <a href="{{ route('shop.banner.index') }}"
                            class="subMenu {{ request()->routeIs('shop.banner.*') ? 'active' : '' }}">
                            {{ __('Promotional Banner') }}
                        </a>
                    @endhasPermission
                @endif
            </div>
        </div>
    </li>
@endhasPermission

@hasPermission(['shop.bulk-product-import.index', 'shop.bulk-product-export.index', 'shop.gallery.index'])
    <li>
        <a class="menu {{ request()->routeIs('shop.bulk-product-export.*', 'shop.bulk-product-import.*', 'shop.gallery.*') ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#bulkToolsMenu">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/download.svg') }}" alt="icon" loading="lazy" />
                {{ __('Bulk Tools & Gallery') }}
            </span>
            <img src="{{ asset('assets/icons-admin/caret-down.svg') }}" alt="" class="downIcon" loading="lazy" />
        </a>
        <div class="collapse dropdownMenuCollapse {{ request()->routeIs('shop.bulk-product-export.*', 'shop.bulk-product-import.*', 'shop.gallery.*') ? 'show' : '' }}" id="bulkToolsMenu">
            <div class="listBar">
                @hasPermission('shop.bulk-product-export.index')
                    <a href="{{ route('shop.bulk-product-export.index') }}"
                        class="subMenu {{ request()->routeIs('shop.bulk-product-export.*') ? 'active' : '' }}">
                        {{ __('Bulk Export') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.bulk-product-import.index')
                    <a href="{{ route('shop.bulk-product-import.index') }}"
                        class="subMenu {{ request()->routeIs('shop.bulk-product-import.*') ? 'active' : '' }}">
                        {{ __('Bulk Import') }}
                    </a>
                @endhasPermission
                @hasPermission('shop.gallery.index')
                    <a href="{{ route('shop.gallery.index') }}"
                        class="subMenu {{ request()->routeIs('shop.gallery.*') ? 'active' : '' }}">
                        {{ __('Gallery Import') }}
                    </a>
                @endhasPermission
            </div>
        </div>
    </li>
@endhasPermission

@hasPermission('shop.profile.index')
    <li>
        <a class="menu {{ $request->routeIs('shop.profile.*') ? 'active' : '' }}"
            href="{{ route('shop.profile.index') }}">
            <span>
                <img class="menu-icon" src="{{ asset('assets/icons-admin/user-circle.svg') }}" alt="icon" loading="lazy" />
                {{ __('Store Profile') }}
            </span>
        </a>
    </li>
@endhasPermission

<li>
    <a href="javascript:void(0)" class="menu logout">
        <span>
            <img class="menu-icon" src="{{ asset('assets/icons-admin/log-out.svg') }}" alt="icon" loading="lazy" />
            {{ __('Logout Account') }}
        </span>
    </a>
</li>
