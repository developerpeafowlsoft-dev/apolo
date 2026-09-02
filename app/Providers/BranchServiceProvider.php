<?php

namespace App\Providers;

use App\Services\BranchContext;
use Illuminate\Support\ServiceProvider;

class BranchServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BranchContext::class, function ($app) {
            return new BranchContext($app['config']->get('branch', []));
        });
    }

    public function boot(): void
    {
        $branchContext = app(BranchContext::class);

        if ($branchContext->isBranch()) {
            \App\Models\Order::observe(\App\Observers\BranchSyncObserver::class);
            \App\Models\Voucher::observe(\App\Observers\BranchSyncObserver::class);
            \App\Models\ProductPurchase::observe(\App\Observers\BranchSyncObserver::class);
        }
    }
}
