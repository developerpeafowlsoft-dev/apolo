<?php

use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\Shop\Auth\LoginController;
use App\Http\Controllers\Shop\BankMasterController;
use App\Http\Controllers\Shop\BannerController;
use App\Http\Controllers\Shop\BrandController;
use App\Http\Controllers\Shop\BulkProductExportController;
use App\Http\Controllers\Shop\BulkProductImportController;
use App\Http\Controllers\Shop\CashierReportController;
use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\ColorController;
use App\Http\Controllers\Shop\CustomerMessageController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\DesignMasterController;
use App\Http\Controllers\Shop\EmployeeController;
use App\Http\Controllers\Shop\FlashSaleController;
use App\Http\Controllers\Shop\GalleryController;
use App\Http\Controllers\Shop\InwardProductController;
use App\Http\Controllers\Shop\PurchaseController;
use App\Http\Controllers\Shop\NotificationController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\POSController;
use App\Http\Controllers\Shop\POSPaymentAttemptController;
use App\Http\Controllers\Shop\PosPaymentReconciliationController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\ProfileController;
use App\Http\Controllers\Shop\PromoVoucherController;
use App\Http\Controllers\Shop\SizeController;
use App\Http\Controllers\Shop\SubCategoryController;
use App\Http\Controllers\Shop\SubscriptionController;
use App\Http\Controllers\Shop\UnitController;
use App\Http\Controllers\Shop\VoucherController;
use App\Http\Controllers\Shop\WithdrawController;
use App\Http\Controllers\Shop\AccountBalanceController;
use App\Http\Controllers\Shop\AccountMasterController;
use App\Http\Controllers\Shop\CounterMasterController;
use App\Http\Controllers\Shop\TDSMasterController;
use App\Http\Controllers\Shop\HSNMasterController;
use App\Http\Controllers\Shop\ItemMasterController;
use App\Http\Controllers\Shop\SalesmanController;
use App\Http\Controllers\Shop\MaterialController;
use App\Http\Controllers\Shop\MasterController;
use Illuminate\Support\Facades\Route;
use Twilio\Rest\Chat;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
*/

Route::name('shop.')->group(function () {

    Route::controller(OrderController::class)->group(function () {
        Route::get('/download-invoice/{id}', 'downloadInvoice')->name('download-invoice');
        Route::get('/payment-slip/{id}/download', 'paymentSlip')->name('payment-slip');
    });

    // Login
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login')->middleware('guest');
        Route::post('/login', 'login')->name('login.submit');
        Route::get(('/register'), 'create')->name('register');
        Route::post(('/register'), 'store')->name('register.submit');
    });

    Route::middleware(['authShop', 'checkPermission'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

        // Subscription
        Route::controller(SubscriptionController::class)->group(function () {
            Route::get('/subscription', 'index')->name('subscription.index');
            Route::post('/subscription/purchase', 'purchase')->name('subscription.purchase');
        });

        // banner
        Route::controller(BannerController::class)->group(function () {
            Route::get('/banners', 'index')->name('banner.index');
            Route::get('/banner/create', 'create')->name('banner.create');
            Route::post('/banner/store', 'store')->name('banner.store');
            Route::get('/banner/{banner}/edit', 'edit')->name('banner.edit');
            Route::put('/banner/{banner}/update', 'update')->name('banner.update');
            Route::get('/banner/{banner}/toggle', 'statusToggle')->name('banner.toggle');
            Route::get('/banner/{banner}/destroy', 'destroy')->name('banner.destroy');
        });

        // Orders
        Route::controller(OrderController::class)->group(function () {
            Route::get('/orders/{status?}', 'index')->name('order.index');
            Route::get('/orders/{order}/show', 'show')->name('order.show');
            Route::get('/orders/{order}/status-change', 'statusChange')->name('order.status.change');
            Route::get('/orders/{order}/payment-status-toggle', 'paymentStatusToggle')->name('order.payment.status.toggle');
            Route::post('/orders/{order}/create-shipment', 'createShipment')->name('order.create-shipment');
            Route::get('/orders/{order}/shiprocket/couriers', 'getShiprocketCouriers')->name('order.shiprocket.couriers');
            Route::post('/orders/{order}/shiprocket/assign-courier', 'assignShiprocketCourier')->name('order.shiprocket.assign-courier');
            Route::post('/orders/{order}/shiprocket/schedule-pickup', 'scheduleShiprocketPickup')->name('order.shiprocket.schedule-pickup');
            Route::get('/orders/{order}/shiprocket/track', 'trackShiprocketShipment')->name('order.shiprocket.track');
        });

        // Categories
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'index')->name('category.index');
            Route::get('/category/create', 'create')->name('category.create');
            Route::post('/category/store', 'store')->name('category.store');
            Route::get('/category/{category}/edit', 'edit')->name('category.edit');
            Route::put('/category/{category}/update', 'update')->name('category.update');
            Route::delete('/category/{category}/destroy', 'destroy')->name('category.destroy');
            Route::get('/category/{category}/toggle', 'statusToggle')->name('category.toggle');
        });

        // sub categories route
        Route::controller(SubCategoryController::class)->group(function () {
            Route::get('/subcategories', 'index')->name('subcategory.index');
            Route::get('/subcategory/create', 'create')->name('subcategory.create');
            Route::post('/subcategory/store', 'store')->name('subcategory.store');
            Route::get('/subcategory/{subCategory}/edit', 'edit')->name('subcategory.edit');
            Route::put('/subcategory/{subCategory}/update', 'update')->name('subcategory.update');
            Route::delete('/subcategory/{subCategory}/destroy', 'destroy')->name('subcategory.destroy');
            Route::get('/subcategory/{subCategory}/toggle', 'statusToggle')->name('subcategory.toggle');
        });

        // brand
        Route::controller(BrandController::class)->group(function () {
            Route::get('/brands', 'index')->name('brand.index');
            Route::post('/brand/store', 'store')->name('brand.store');
            Route::put('/brand/{brand}/update', 'update')->name('brand.update');
            Route::delete('/brand/{brand}/destroy', 'destroy')->name('brand.destroy');
            Route::get('/brand/{brand}/toggle', 'statusToggle')->name('brand.toggle');
        });

        // color
        Route::controller(ColorController::class)->group(function () {
            Route::get('/colors', 'index')->name('color.index');
            Route::post('/color/store', 'store')->name('color.store');
            Route::put('/color/{color}/update', 'update')->name('color.update');
            Route::delete('/color/{color}/destroy', 'destroy')->name('color.destroy');
            Route::get('/color/{color}/toggle', 'statusToggle')->name('color.toggle');
        });

        // size
        Route::controller(SizeController::class)->group(function () {
            Route::get('/sizes', 'index')->name('size.index');
            Route::post('/size/store', 'store')->name('size.store');
            Route::put('/size/{size}/update', 'update')->name('size.update');
            Route::delete('/size/{size}/destroy', 'destroy')->name('size.destroy');
            Route::get('/size/{size}/toggle', 'statusToggle')->name('size.toggle');
        });

        // unit
        Route::controller(UnitController::class)->group(function () {
            Route::get('/units', 'index')->name('unit.index');
            Route::post('/unit/store', 'store')->name('unit.store');
            Route::put('/unit/{unit}/update', 'update')->name('unit.update');
            Route::delete('/unit/{unit}/destroy', 'destroy')->name('unit.destroy');
            Route::get('/unit/{unit}/toggle', 'statusToggle')->name('unit.toggle');
        });

        // material
        Route::controller(MaterialController::class)->group(function () {
            Route::get('/material', 'index')->name('material.index');
            Route::post('/material/store', 'store')->name('material.store');
            Route::put('/material/{material}/update', 'update')->name('material.update');
            Route::delete('/material/{material}/destroy', 'destroy')->name('material.destroy');
            Route::get('/material/{material}/toggle', 'statusToggle')->name('material.toggle');
        });

        // Products
        Route::controller(ProductController::class)->group(function () {
            Route::get('/products', 'index')->name('product.index');
            Route::get('/product/create', 'create')->name('product.create');
            Route::post('/product/store', 'store')->name('product.store');
            Route::get('/product/{product}/edit', 'edit')->name('product.edit');
            Route::put('/product/{product}/update', 'update')->name('product.update');
            Route::get('/product/{product}/show', 'show')->name('product.show');
            Route::get('/product/{product}/toggle', 'statusToggle')->name('product.toggle');
            Route::delete('/product/{product}/destroy', 'destroy')->name('product.destroy');
            Route::get('/product/{product}/thumbnail/{media}/delete', 'thumbnailDestroy')->name('product.remove.thumbnail');
            Route::get('/product/{product}/generate-barcode', 'generateBarcode')->name('product.barcode');
        });

        // profile
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'index')->name('profile.index');
            Route::get('/profile/edit', 'edit')->name('profile.edit');
            Route::put('/profile/update', 'update')->name('profile.update');
            Route::get('/profile/change-password', 'changePassword')->name('profile.change-password');
            Route::put('/profile/change-password/update', 'updatePassword')->name('profile.change-password.update');
        });

        // Promo Codes
        Route::controller(PromoVoucherController::class)->group(function () {
            Route::get('/vouchers', 'index')->name('voucher.index');
            Route::get('/voucher/create', 'create')->name('voucher.create');
            Route::post('/voucher/store', 'store')->name('voucher.store');
            Route::get('/voucher/{coupon}/edit', 'edit')->name('voucher.edit');
            Route::put('/voucher/{coupon}/update', 'update')->name('voucher.update');
            Route::get('/voucher/{coupon}/destroy', 'destroy')->name('voucher.destroy');
            Route::get('/voucher/{coupon}/toggle', 'statusToggle')->name('voucher.toggle');
        });

        // Logout
        Route::controller(LoginController::class)->group(function () {
            Route::post('/logout', 'logout')->name('logout');
        });

        // notification
        Route::controller(NotificationController::class)->group(function () {
            Route::get('/new-notifications', 'index')->name('dashboard.notification');
            Route::get('/notifications', 'show')->name('notification.show');
            Route::get('/notification/{notification}/read', 'markAsRead')->name('notification.read');
            Route::get('/notification/{notification}/destroy', 'destroy')->name('notification.destroy');
            Route::get('/notification/read-all', 'markAllAsRead')->name('notification.readAll');
        });

        // withdrawal route
        Route::controller(WithdrawController::class)->group(function () {
            Route::get('/withdraw', 'index')->name('withdraw.index');
            Route::post('/withdraw/store', 'store')->name('withdraw.store');
            Route::get('/withdraw/{withdraw}/delete', 'delete')->name('withdraw.delete');
            Route::get('/withdraw/{withdraw}/show', 'show')->name('withdraw.show');
        });
        // bulk product route
        Route::controller(BulkProductImportController::class)->group(function () {
            Route::get('/bulk-product-import', 'index')->name('bulk-product-import.index');
            Route::post('/bulk-product-import/store', 'store')->name('bulk-product-import.store');
            Route::get('/bulk-product-format-export', 'formatExport')->name('bulk-product-import.formatExport');
            Route::post('/bulk-product-import/export', 'export')->name('bulk-product-import.export');
        });

        // bulk product export route
        Route::controller(BulkProductExportController::class)->group(function () {
            Route::get('/bulk-product-export', 'index')->name('bulk-product-export.index');
            Route::post('/bulk-product-export/export', 'export')->name('bulk-product-export.export');
            Route::get('/bulk-product-export/demo', 'demoExport')->name('bulk-product-export.demo');
        });

        // gallery route
        Route::controller(GalleryController::class)->group(function () {
            Route::get('/gallery', 'index')->name('gallery.index');
            Route::get('/gallery/create', 'create')->name('gallery.create');
            Route::post('/gallery/store', 'store')->name('gallery.store');
        });

        // POS routes
        Route::controller(POSController::class)->group(function () {
            Route::get('/pos', 'index')->name('pos.index');
            Route::get('/pos/sales', 'sales')->name('pos.sales');
            Route::get('/pos/draft', 'draft')->name('pos.draft');
            Route::get('/pos/draft/{posCart}/delete', 'draftDelete')->name('pos.draft.delete');

            // others
            Route::get('/pos/{order}/invoice', 'invoice')->name('pos.invoice');
            Route::get('/pos/{order}/thermal-preview', 'thermalPreview')->name('pos.thermalPreview');
            Route::post('/fetch-products', 'getProduct')->name('pos.product');
            Route::post('/add-to-cart', 'addToCart')->name('pos.addToCart');
            Route::post('/fetch-cart', 'getCart')->name('pos.getCart');
            Route::post('/update-cart', 'updateCart')->name('pos.updateCart');
            Route::post('/remove-cart', 'removeCart')->name('pos.removeCart');
            Route::post('/apply-coupon', 'applyCoupon')->name('pos.applyCoupon');
            Route::post('/remove-coupon', 'removeCoupon')->name('pos.removeCoupon');
            Route::post('/store-order', 'storeOrder')->name('pos.submitOrder');
            Route::post('/customer-store', 'storeCustomer')->name('pos.customerStore');
            
            // New GRetail POS Grid Routes
            Route::get('/pos/resolve-barcode/{barcode}', 'resolveBarcode')->name('pos.resolveBarcode');
            Route::get('/pos/search-products', 'searchProducts')->name('pos.searchProducts');
            Route::get('/pos/search-customer/{phone}', 'searchCustomer')->name('pos.searchCustomer');
            Route::post('/pos/checkout-grid', 'checkoutGrid')->name('pos.checkoutGrid');
            Route::get('/pos/counters', 'getCounters')->name('pos.counters');
            Route::post('/pos/hold', 'holdOrder')->name('pos.hold');
            Route::get('/pos/holds', 'getHolds')->name('pos.getHolds');
            Route::delete('/pos/hold/{id}', 'recallHold')->name('pos.recallHold');
            Route::get('/pos/return-lookup/{invoice}', 'lookupReturnInvoice')->name('pos.returnLookup');
            Route::post('/pos/return', 'processReturn')->name('pos.returnSubmit');
            Route::get('/pos/return/{id}/print', 'printReturnReceipt')->name('pos.return.print');
            Route::get('/pos/shift-status', 'checkShiftStatus')->name('pos.shiftStatus');
            Route::post('/pos/shift-open', 'openShift')->name('pos.shiftOpen');
            Route::post('/pos/shift-close', 'closeShift')->name('pos.shiftClose');
            Route::get('/pos/shift-stats', 'getShiftStats')->name('pos.shiftStats');

            // Provider-Independent EDC Terminal Bridge routes
            Route::controller(POSPaymentAttemptController::class)->group(function () {
                Route::get('/pos/terminals/current', 'currentTerminal')->name('pos.terminals.current');
                Route::post('/pos/payments/initiate', 'initiate')->name('pos.payments.initiate');
                Route::post('/pos/payments/{attempt}/acknowledge', 'acknowledge')->name('pos.payments.acknowledge');
                Route::post('/pos/payments/{attempt}/result', 'result')->name('pos.payments.result');
                Route::get('/pos/payments/{attempt}/status', 'status')->name('pos.payments.status');
                Route::post('/pos/payments/{attempt}/cancel', 'cancel')->name('pos.payments.cancel');
                Route::post('/pos/payments/{attempt}/reconcile', 'reconcile')->name('pos.payments.reconcile');
            });

            // Terminal Payment Reconciliation routes
            Route::controller(PosPaymentReconciliationController::class)->group(function () {
                Route::get('/pos/reconciliation', 'index')->name('pos.reconciliation.index');
                Route::post('/pos/reconciliation/{id}/recheck', 'recheck')->name('pos.reconciliation.recheck');
                Route::post('/pos/reconciliation/{id}/mark-failed', 'markFailed')->name('pos.reconciliation.markFailed');
                Route::get('/pos/reconciliation/export', 'export')->name('pos.reconciliation.export');
            });

            // Enterprise Hold Bill Management routes
            Route::post('/pos/hold-bills', 'saveHoldBill')->name('pos.holdBills.save');
            Route::get('/pos/hold-bills', 'listHoldBills')->name('pos.holdBills.list');
            Route::delete('/pos/hold-bills/{id}', 'deleteHoldBill')->name('pos.holdBills.delete');
            Route::post('/pos/hold-bills/{id}/restore', 'restoreHoldBill')->name('pos.holdBills.restore');
            Route::post('/pos/hold-bills/{id}/duplicate', 'duplicateHoldBill')->name('pos.holdBills.duplicate');
            Route::get('/pos/hold-bills/{id}/print', 'printHoldReceipt')->name('pos.holdBills.print');

            // Previous Bill History routes
            Route::get('/pos/history', 'salesHistoryIndex')->name('pos.history.index');
            Route::get('/pos/history-stats', 'salesHistoryStats')->name('pos.history.stats');
            Route::post('/pos/history/{id}/void', 'salesHistoryVoid')->name('pos.history.void');
            Route::post('/pos/history/{id}/duplicate', 'salesHistoryDuplicate')->name('pos.history.duplicate');
            Route::get('/pos/history-export', 'salesHistoryExport')->name('pos.history.export');
            Route::get('/branch/sync-status', 'syncStatus')->name('branch.syncStatus');
        });

        // Cashier Performance / Billing Report
        Route::controller(CashierReportController::class)->group(function () {
            Route::get('/reports/cashier-performance', 'index')->name('cashier-report.index');
            Route::get('/reports/cashier-performance/export', 'exportCSV')->name('cashier-report.export');
        });

        // employee management route
        Route::controller(EmployeeController::class)->group(function () {
            Route::get('/employees', 'index')->name('employee.index');
            Route::get('/employee/create', 'create')->name('employee.create');
            Route::post('/employee/store', 'store')->name('employee.store');
            Route::put('/employee/{user}/update', 'update')->name('employee.update');
            Route::get('/employee/{user}/destroy', 'destroy')->name('employee.destroy');
            Route::post('employee/{user}/reset-password', 'resetPassword')->name('employee.reset-password');
            Route::get('/employee/{user}/permission', 'permission')->name('employee.permission');
            Route::post('/employee/{user}/permission', 'updatePermission')->name('employee.permission.update');
        });

        // flash sale route
        Route::controller(FlashSaleController::class)->group(function () {
            Route::get('/flash-sale', 'index')->name('flashSale.index');
            Route::get('/flash-sale/{flashSale}/show', 'show')->name('flashSale.show');
            Route::post('/flash-sale/{flashSale}/product-store', 'productStore')->name('flashSale.productStore');
            Route::get('/flash-sale/{flashSale}/product/{product}/remove', 'productRemove')->name('flashSale.productRemove');
            Route::put('/flash-sale/{flashSale}/product/{product}/edit', 'update')->name('flashSale.product.edit');
        });

        // customer messages route
        Route::controller(ChatController::class)->group(function () {
            Route::get('/customer-chats', 'index')->name('customer.chat.index');
            Route::get('/get-users', 'getUsers');
            Route::get('/get-message', 'getMessageAdmin');
            Route::post('/send-message', 'sendMessageAdmin');
        });

        // Account Master
        Route::controller(AccountMasterController::class)->middleware('checkShopOwner')->group(function (){
            Route::get('/account-master','index')->name('accountMaster.index');
            Route::get('/account-master/create','create')->name('accountMaster.create');
            Route::post('/account-master/store', 'store')->name('accountMaster.store');
            Route::get('/account-master/{accountMaster}/edit', 'edit')->name('accountMaster.edit');
            Route::put('/account-master/{accountMaster}/update', 'update')->name('accountMaster.update');
            Route::get('/account-master/{accountMaster}/toggle', 'statusToggle')->name('accountMaster.toggle');

            Route::get('/cities/search', 'citiesSearch')->name('cities.search');
            Route::get('/country/search', 'countrySearch')->name('country.search');
            Route::post('/get-states', 'getStates')->name('get.states');
            Route::post('/get-cities', 'getCities')->name('get.cities');

            // GST Check
            Route::post('/gst/check', 'check')->name('gst.check');
        });

        // Statutory GST & Financial Reports Dashboard
        Route::controller(\App\Http\Controllers\Shop\AccountingReportController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/accounting/dashboard', 'accountingDashboard')->name('reports.accountingDashboard');
            Route::get('/accounting/vouchers', 'vouchers')->name('reports.vouchers');
            Route::get('/accounting/general-ledger', 'generalLedger')->name('reports.generalLedger');
            Route::get('/accounting/purchase-returns', 'purchaseReturns')->name('reports.purchaseReturns');
            Route::get('/accounting/supplier-payments', 'supplierPayments')->name('reports.supplierPayments');
            Route::get('/accounting/cod-reconciliation', 'codReconciliation')->name('reports.codReconciliation');
            Route::get('/accounting/inventory-valuation', 'inventoryValuation')->name('reports.inventoryValuation');
            Route::get('/reports/gst-dashboard', 'gstDashboard')->name('reports.gstDashboard');
            Route::get('/reports/gstr1-export', 'exportGstr1Json')->name('reports.gstr1Export');
            Route::post('/reports/gstr2b-reconcile', 'reconcileGstr2b')->name('reports.gstr2bReconcile');
            Route::get('/reports/financial-statements', 'financialStatements')->name('reports.financialStatements');
            Route::get('/reports/bank-reconciliation', 'bankReconciliation')->name('reports.bankReconciliation');
            Route::get('/reports/outstanding-ageing', 'outstandingAgeing')->name('reports.outstandingAgeing');
        });

        // Dynamic Reports & Visual Analytics Engine
        Route::controller(\App\Http\Controllers\Shop\AnalyticsReportController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/reports/visual-analytics', 'visualAnalytics')->name('reports.visualAnalytics');
            Route::get('/reports/counter-productivity', 'counterProductivity')->name('reports.counterProductivity');
        });

        // Account Balance
        Route::controller(AccountBalanceController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/account-balance', 'index')->name('accountBalance.index');
            Route::post('/account-balance/store', 'store')->name('accountBalance.store');
        });

        // Bank Master
        Route::controller(BankMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/bank-master','index')->name('bankMaster.index');
            Route::get('/bank-master/create','create')->name('bankMaster.create');
            Route::post('/bank-master/store', 'store')->name('bankMaster.store');
            Route::get('/bank-master/{bankMaster}/edit', 'edit')->name('bankMaster.edit');
            Route::put('/bank-master/{bankMaster}/update', 'update')->name('bankMaster.update');
            Route::get('/bank-master/{bankMaster}/toggle', 'statusToggle')->name('bankMaster.toggle');
        });

        // Counter Master
        Route::controller(CounterMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/counter-master','index')->name('counterMaster.index');
            Route::get('/counter-master/create','create')->name('counterMaster.create');
            Route::post('/counter-master/store', 'store')->name('counterMaster.store');
            Route::get('/counter-master/{counterMaster}/edit', 'edit')->name('counterMaster.edit');
            Route::put('/counter-master/{counterMaster}/update', 'update')->name('counterMaster.update');
            Route::get('/counter-master/{counterMaster}/toggle', 'statusToggle')->name('counterMaster.toggle');
        });

        // TDS Master
        Route::controller(TDSMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/tds-master','index')->name('tdsMaster.index');
            Route::get('/tds-master/create','create')->name('tdsMaster.create');
            Route::post('/tds-master/store', 'store')->name('tdsMaster.store');
            Route::get('/tds-master/{tdsMaster}/edit', 'edit')->name('tdsMaster.edit');
            Route::put('/tds-master/{tdsMaster}/update', 'update')->name('tdsMaster.update');
            Route::get('/tds-master/{tdsMaster}/toggle', 'statusToggle')->name('tdsMaster.toggle');
        });

        // HSN Code Master
        Route::controller(HSNMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/hsn-master','index')->name('hsnMaster.index');
            Route::get('/hsn-master/create','create')->name('hsnMaster.create');
            Route::post('/hsn-master/store', 'store')->name('hsnMaster.store');
            Route::get('/hsn-master/{hsnMaster}/edit', 'edit')->name('hsnMaster.edit');
            Route::put('/hsn-master/{hsnMaster}/update', 'update')->name('hsnMaster.update');
            Route::get('/hsn-master/{hsnMaster}/toggle', 'statusToggle')->name('hsnMaster.toggle');
        });

        // Design Master
        Route::controller(DesignMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/design-master','index')->name('designMaster.index');
//            Route::get('/hsn-master/create','create')->name('hsnMaster.create');
            Route::post('/design-master/store', 'store')->name('designMaster.store');
            Route::get('/design-master/{designMaster}/edit', 'edit')->name('designMaster.edit');
            Route::put('/design-master/{designMaster}/update', 'update')->name('designMaster.update');
            Route::get('/design-master/{designMaster}/destroy', 'destroy')->name('designMaster.destroy');

            Route::get('/design-master/{designMaster}/toggle', 'statusToggle')->name('designMaster.toggle');

            Route::get('/design-master/modalData','modalData')->name('designMaster.modalData');
        });

        // Item Master
        Route::controller(ItemMasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/item-master','index')->name('itemMaster.index');
//            Route::get('/item-master/create','create')->name('itemMaster.create');
            Route::post('/item-master/store', 'store')->name('itemMaster.store');
            Route::get('/item-master/{itemMaster}/edit', 'edit')->name('itemMaster.edit');
            Route::put('/item-master/{itemMaster}/update', 'update')->name('itemMaster.update');
            Route::get('/item-master/{itemMaster}/destroy', 'destroy')->name('itemMaster.destroy');

            Route::get('/item-master/{itemMaster}/isOnlineProduct', 'onlineProductToggle')->name('itemMaster.onlineProductToggle');

            Route::get('/item-master/modalData','modalData')->name('itemMaster.modalData');
        });

        // Inward Product
        Route::controller(InwardProductController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/inward-product','index')->name('inwardProduct.index');
//            Route::get('/hsn-master/create','create')->name('hsnMaster.create');
            Route::post('/inward-product/store', 'store')->name('inwardProduct.store');
            Route::get('/inward-product/{inwardProduct}/edit', 'edit')->name('inwardProduct.edit');
            Route::put('/inward-product/{inwardProduct}/update', 'update')->name('inwardProduct.update');
//            Route::get('/design-master/{designMaster}/destroy', 'destroy')->name('designMaster.destroy');
//

//            Route::get('/design-master/{designMaster}/toggle', 'statusToggle')->name('designMaster.toggle');
            Route::get('/inward-product/{inwardProduct}/destroy', 'destroy')->name('inwardProduct.destroy');
            Route::get('/inward-product/{inwardProduct}/destroy-inward-product', 'destroyInwardProduct')->name('inwardProduct.destroyInwardProduct');
            Route::get('/inward-product/list','inwardProduct')->name('inwardProduct.list');
            Route::get('/inward-product/next-kachi-sequence', 'getNextKachiSequence')->name('inwardProduct.nextKachiSequence');
//
            Route::get('/inward-product/designDataGet','designDataGet')->name('designMaster.designDataGet');

            Route::get('/inward-product/{designId}/get-old-price', 'getOldProductPrice')->name('inwardProduct.getOldProductPrice');

            Route::get('/inward-product/{id}/barcode-product-load', 'inwardGetByProductBarcode')->name('inwardProductByBarcode.productLoad');
            Route::post('/inward-product/{inwardInvoiceId}/{inwardProductId}/{productId}/product-wise-barcode-generate', 'productWiseBarcodeGenerate')->name('inwardProductByBarcode.productWiseBarcodeGenerate');
            Route::get('/product-barcode/{inwardInvoiceId}/{inwardProductId}/{productId}/barcode-load', 'productByBarcodeGet')->name('productByBarcodeGet.barcodeLoad');

            Route::get('/product-barcode/{id}/generate', 'productBarcodeGenerate')->name('productBarcodeGenerate.generate');
            Route::post('/product-barcode/generate-multiple', 'productBarcodeGenerateMultiple')->name('productBarcodeGenerateMultiple.generateMultiple');

        });

        // New Code By Spider
        Route::controller(PurchaseController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/purchase-product','index')->name('purchaseProduct.index');
            Route::get('/purchase-search', 'searchPurchase')->name('purchaseProduct.search');
            Route::post('/purchase-product/store', 'store')->name('purchaseProduct.store');
            Route::get('/purchase-product/{purchaseProduct}/edit', 'edit')->name('purchaseProduct.edit');
//            Route::put('/inward-product/{inwardProduct}/update', 'update')->name('inwardProduct.update');
//
//
//            Route::get('/inward-product/{inwardProduct}/destroy', 'destroy')->name('inwardProduct.destroy');
//            Route::get('/inward-product/{inwardProduct}/destroy-inward-product', 'destroyInwardProduct')->name('inwardProduct.destroyInwardProduct');
            Route::get('/purchase-product/list','purchaseProduct')->name('purchaseProduct.list');
            Route::get('/purchase-product/view/{id}', 'view')->name('purchaseProduct.view');
            Route::get('/purchase-product/is-online/{id}', 'onlineProductToggle')->name('purchaseProduct.onlineProductToggle');
            Route::get('/purchase-product/toggle-item-online/{inwardProductId}', 'toggleSingleItemOnline')->name('purchaseProduct.toggleItemOnline');
////
//            Route::get('/inward-product/designDataGet','designDataGet')->name('designMaster.designDataGet');
//
//            Route::get('/inward-product/{designId}/get-old-price', 'getOldProductPrice')->name('inwardProduct.getOldProductPrice');
//
//            Route::get('/inward-product/{id}/barcode-product-load', 'inwardGetByProductBarcode')->name('inwardProductByBarcode.productLoad');
//            Route::post('/inward-product/{inwardInvoiceId}/{inwardProductId}/{productId}/product-wise-barcode-generate', 'productWiseBarcodeGenerate')->name('inwardProductByBarcode.productWiseBarcodeGenerate');
//            Route::get('/product-barcode/{inwardInvoiceId}/{inwardProductId}/{productId}/barcode-load', 'productByBarcodeGet')->name('productByBarcodeGet.barcodeLoad');
//
//            Route::get('/product-barcode/{id}/generate', 'productBarcodeGenerate')->name('productBarcodeGenerate.generate');
//            Route::post('/product-barcode/generate-multiple', 'productBarcodeGenerateMultiple')->name('productBarcodeGenerateMultiple.generateMultiple');

        });
        // Close

        Route::controller(MasterController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/master-season','masterSeason')->name('masterSeason.seasonFind');
            Route::get('/master-agent','masterAgent')->name('masterAgent.agentFind');
            Route::get('/master-transport','masterTransport')->name('masterTransport.transportFind');
            Route::get('/master-deliveryBy','masterdeliveryBy')->name('masterdeliveryBy.deliveryByFind');
        });

        // Salesman
        Route::controller(SalesmanController::class)->middleware('checkShopOwner')->group(function () {
            Route::get('/salesman','index')->name('salesman.index');
            Route::get('/salesman/create','create')->name('salesman.create');
            Route::post('/salesman/store', 'store')->name('salesman.store');
            Route::get('/salesman/{salesman}/edit', 'edit')->name('salesman.edit');
            Route::put('/salesman/{salesman}/update', 'update')->name('salesman.update');
            Route::get('/salesman/{salesman}/toggle', 'statusToggle')->name('salesman.toggle');
        });

    });
});
