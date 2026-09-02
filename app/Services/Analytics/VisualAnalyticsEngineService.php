<?php

namespace App\Services\Analytics;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CounterMaster;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisualAnalyticsEngineService
{
    /**
     * Generate Sales & Stock Visual Analytics Data for Charts.
     *
     * @param int $shopId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array Analytical metrics for Chart.js
     */
    public function getSalesAndStockAnalytics(int $shopId, string $startDate, string $endDate): array
    {
        // 1. Daily Sales Trend (excluding cancelled orders)
        $salesTrend = Order::where('shop_id', $shopId)
            ->where('order_status', '!=', 'Cancelled')
            ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as sale_date, SUM(payable_amount) as total_sales, COUNT(*) as order_count')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'ASC')
            ->get();

        $labels = [];
        $salesData = [];
        $orderCounts = [];

        foreach ($salesTrend as $st) {
            $labels[] = date('d M', strtotime($st->sale_date));
            $salesData[] = round((float)$st->total_sales, 2);
            $orderCounts[] = (int)$st->order_count;
        }

        // 2. Payment Method Distribution (Cash vs Card vs UPI)
        $paymentDist = Order::where('shop_id', $shopId)
            ->where('order_status', '!=', 'Cancelled')
            ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('payment_method, SUM(payable_amount) as total_amount')
            ->groupBy('payment_method')
            ->get();

        $paymentLabels = [];
        $paymentAmounts = [];
        foreach ($paymentDist as $pd) {
            $paymentLabels[] = $pd->payment_method ? (is_object($pd->payment_method) ? $pd->payment_method->value : (string)$pd->payment_method) : 'Cash';
            $paymentAmounts[] = round((float)$pd->total_amount, 2);
        }

        // 3. Fast Moving vs Slow Moving Products (Top 5 & Bottom 5)
        $topProducts = Product::where('shop_id', $shopId)
            ->withCount(['barcodes as sold_count' => function ($q) {
                $q->where('is_sold', 1);
            }])
            ->orderBy('sold_count', 'DESC')
            ->take(5)
            ->get();

        $topProductData = [];
        foreach ($topProducts as $tp) {
            $topProductData[] = [
                'name' => $tp->name,
                'sold_count' => (int)($tp->sold_count ?? 0),
                'stock' => $tp->available_stock,
            ];
        }

        return [
            'period' => "{$startDate} to {$endDate}",
            'sales_trend' => [
                'labels' => $labels,
                'sales' => $salesData,
                'order_counts' => $orderCounts,
            ],
            'payment_distribution' => [
                'labels' => $paymentLabels,
                'amounts' => $paymentAmounts,
            ],
            'top_products' => $topProductData,
        ];
    }

    /**
     * Generate Customer RFM Segmentation Analysis (Recency, Frequency, Monetary).
     *
     * @param int $shopId
     * @return array RFM Customer Clusters
     */
    public function getCustomerRfmSegmentation(int $shopId): array
    {
        $customers = Customer::whereHas('user')->with('user')->get();
        $now = Carbon::now();

        $vip = 0;
        $frequent = 0;
        $atRisk = 0;
        $churned = 0;

        foreach ($customers as $cust) {
            $orders = Order::where('customer_id', $cust->id)->get();
            if ($orders->isEmpty()) continue;

            $totalSpend = $orders->sum('payable_amount');
            $orderCount = $orders->count();
            $lastOrderDate = Carbon::parse($orders->max('created_at'));
            $daysSinceLastOrder = $lastOrderDate->diffInDays($now);

            if ($totalSpend >= 20000 || $orderCount >= 10) {
                $vip++;
            } elseif ($daysSinceLastOrder <= 30) {
                $frequent++;
            } elseif ($daysSinceLastOrder <= 90) {
                $atRisk++;
            } else {
                $churned++;
            }
        }

        return [
            'vip_customers' => $vip,
            'frequent_customers' => $frequent,
            'at_risk_customers' => $atRisk,
            'churned_customers' => $churned,
            'total_segmented' => ($vip + $frequent + $atRisk + $churned),
        ];
    }

    /**
     * Generate Counter & Cashier Productivity Analytics.
     *
     * @param int $shopId
     * @param string $startDate
     * @param string $endDate
     * @return array Counter Performance Matrix
     */
    public function getCounterProductivity(int $shopId, string $startDate, string $endDate): array
    {
        $counters = CounterMaster::where('shop_id', $shopId)->get();
        $matrix = [];

        foreach ($counters as $cnt) {
            $orders = Order::where('shop_id', $shopId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();

            $totalRevenue = $orders->sum('payable_amount');
            $totalCount = $orders->count();
            $avgOrderVal = $totalCount > 0 ? round($totalRevenue / $totalCount, 2) : 0;

            $matrix[] = [
                'counter_name' => $cnt->counter_name ?? "Counter #{$cnt->id}",
                'total_orders' => $totalCount,
                'total_revenue' => round($totalRevenue, 2),
                'avg_order_value' => $avgOrderVal,
                'cash_sales' => round($orders->where('payment_method', 'Cash')->sum('payable_amount'), 2),
                'digital_sales' => round($orders->where('payment_method', '!=', 'Cash')->sum('payable_amount'), 2),
            ];
        }

        return $matrix;
    }
}
