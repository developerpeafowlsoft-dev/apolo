<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Analytics\VisualAnalyticsEngineService;
use Illuminate\Http\Request;

class AnalyticsReportController extends Controller
{
    protected $analyticsService;

    public function __construct(VisualAnalyticsEngineService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function visualAnalytics(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $analytics = $this->analyticsService->getSalesAndStockAnalytics($shop->id, $startDate, $endDate);
        $rfm = $this->analyticsService->getCustomerRfmSegmentation($shop->id);

        return view('shop.reports.visual_analytics', compact('analytics', 'rfm', 'startDate', 'endDate'));
    }

    public function counterProductivity(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $productivity = $this->analyticsService->getCounterProductivity($shop->id, $startDate, $endDate);
        return view('shop.reports.counter_productivity', compact('productivity', 'startDate', 'endDate'));
    }
}
