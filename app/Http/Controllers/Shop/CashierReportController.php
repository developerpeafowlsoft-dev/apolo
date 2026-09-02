<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashierReportController extends Controller
{
    /**
     * Cashier Performance & Billing Report Dashboard
     */
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $period = $request->get('period', 'today');
        $cashierFilter = $request->get('cashier_id', 'all');

        // Resolve Date Range
        $now = now();
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($period === 'today') {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } else if ($period === 'weekly') {
            $startDate = $now->copy()->startOfWeek();
            $endDate = $now->copy()->endOfWeek();
        } else if ($period === 'monthly') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } else if ($period === 'yearly') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } else if ($period === 'custom' && $dateFrom && $dateTo) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } else {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $period = 'today';
        }

        // Base POS Orders Query
        $baseQuery = Order::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('pos_order', true)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter Cashier
        if ($cashierFilter !== 'all') {
            if ($cashierFilter === 'unassigned') {
                $baseQuery->whereNull('cashier_id');
            } else {
                $baseQuery->where('cashier_id', $cashierFilter);
            }
        }

        $allOrders = (clone $baseQuery)
            ->with(['cashier', 'customer.user', 'payments', 'products'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Calculate Per-Cashier Performance Summaries
        $grouped = $allOrders->groupBy(function ($order) {
            return $order->cashier_id ? (string)$order->cashier_id : 'unassigned';
        });

        $cashierPerformances = collect();

        foreach ($grouped as $cashierKey => $orders) {
            $totalBills = $orders->count();
            if ($totalBills === 0) continue;

            $firstBill = $orders->sortBy('created_at')->first();
            $lastBill = $orders->sortBy('created_at')->last();

            $firstBillTime = $firstBill->created_at;
            $lastBillTime = $lastBill->created_at;

            $diffSeconds = $firstBillTime->diffInSeconds($lastBillTime);
            $workingTimeFormatted = $this->formatDuration($diffSeconds);

            $avgTimeBetweenFormatted = 'N/A';
            if ($totalBills > 1 && $diffSeconds > 0) {
                $avgSecBetween = (int)round($diffSeconds / ($totalBills - 1));
                $avgTimeBetweenFormatted = $this->formatDuration($avgSecBetween);
            }

            $grossSales = (float)$orders->sum('payable_amount');
            $avgBillAmt = $totalBills > 0 ? ($grossSales / $totalBills) : 0;

            $cashSales = (float)$orders->filter(function($o) {
                $pm = strtolower($o->payment_method->value ?? $o->payment_method ?? '');
                return str_contains($pm, 'cash');
            })->sum('payable_amount');

            $cardSales = (float)$orders->filter(function($o) {
                $pm = strtolower($o->payment_method->value ?? $o->payment_method ?? '');
                return !str_contains($pm, 'cash');
            })->sum('payable_amount');

            $cancelledOrders = $orders->filter(function($o) {
                $st = strtolower($o->order_status->value ?? $o->order_status ?? '');
                return $st === 'canceled' || $st === 'cancelled' || $st === 'returned';
            });
            $cancelledCount = $cancelledOrders->count();
            $cancelledAmount = (float)$cancelledOrders->sum('payable_amount');

            $netCollected = $grossSales - $cancelledAmount;

            $cashierName = 'Unassigned Cashier';
            if ($cashierKey !== 'unassigned' && $firstBill->cashier) {
                $cashierName = $firstBill->cashier->name;
            }

            $cashierPerformances->push([
                'cashier_id' => $cashierKey,
                'cashier_name' => $cashierName,
                'total_bills' => $totalBills,
                'gross_sales' => $grossSales,
                'avg_bill_amount' => $avgBillAmt,
                'cash_sales' => $cashSales,
                'card_sales' => $cardSales,
                'cancelled_count' => $cancelledCount,
                'cancelled_amount' => $cancelledAmount,
                'net_collected' => $netCollected,
                'first_bill_time' => $firstBillTime->format('Y-m-d h:i A'),
                'last_bill_time' => $lastBillTime->format('Y-m-d h:i A'),
                'working_time' => $workingTimeFormatted,
                'avg_time_between' => $avgTimeBetweenFormatted,
            ]);
        }

        // Overall Summary Statistics
        $totalBillsCount = $allOrders->count();
        $totalSalesAmt = (float)$allOrders->sum('payable_amount');
        $totalCashSales = (float)$allOrders->filter(function($o) {
            $pm = strtolower($o->payment_method->value ?? $o->payment_method ?? '');
            return str_contains($pm, 'cash');
        })->sum('payable_amount');
        $totalCardSales = (float)$allOrders->filter(function($o) {
            $pm = strtolower($o->payment_method->value ?? $o->payment_method ?? '');
            return !str_contains($pm, 'cash');
        })->sum('payable_amount');
        $totalCancelledAmt = (float)$allOrders->filter(function($o) {
            $st = strtolower($o->order_status->value ?? $o->order_status ?? '');
            return $st === 'canceled' || $st === 'cancelled' || $st === 'returned';
        })->sum('payable_amount');
        $totalNetCollected = $totalSalesAmt - $totalCancelledAmt;

        // Paginated Detail List
        $detailOrders = (clone $baseQuery)
            ->with(['cashier', 'customer.user', 'payments', 'products'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get List of Cashiers for Filter Dropdown
        $cashiersList = User::where(function($q) use ($shop) {
                if ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            })
            ->whereHas('roles', function($r) {
                $r->whereIn('name', ['shop', 'admin', 'root', 'staff', 'cashier']);
            })
            ->get(['id', 'name']);

        $currency = generaleSetting('defaultCurrency')?->symbol ?? (generaleSetting('setting')?->currency ?? '₹');

        return view('shop.reports.cashier_performance', compact(
            'period', 'cashierFilter', 'startDate', 'endDate', 'dateFrom', 'dateTo',
            'cashierPerformances', 'totalBillsCount', 'totalSalesAmt',
            'totalCashSales', 'totalCardSales', 'totalCancelledAmt',
            'totalNetCollected', 'detailOrders', 'cashiersList', 'currency'
        ));
    }

    /**
     * Export Cashier Performance / Billing Report to CSV
     */
    public function exportCSV(Request $request)
    {
        $shop = generaleSetting('shop');
        $period = $request->get('period', 'today');
        $cashierFilter = $request->get('cashier_id', 'all');

        $now = now();
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($period === 'today') {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } else if ($period === 'weekly') {
            $startDate = $now->copy()->startOfWeek();
            $endDate = $now->copy()->endOfWeek();
        } else if ($period === 'monthly') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } else if ($period === 'yearly') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } else if ($period === 'custom' && $dateFrom && $dateTo) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } else {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
        }

        $baseQuery = Order::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('pos_order', true)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($cashierFilter !== 'all') {
            if ($cashierFilter === 'unassigned') {
                $baseQuery->whereNull('cashier_id');
            } else {
                $baseQuery->where('cashier_id', $cashierFilter);
            }
        }

        $orders = $baseQuery->with(['cashier', 'customer.user', 'products'])->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Cashier_Billing_Report_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Invoice No', 'Date & Time', 'Cashier Name', 'Customer Name',
            'Items Qty', 'Gross Amount', 'Tax Amount', 'Payable Amount',
            'Payment Method', 'Status'
        ];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $o) {
                fputcsv($file, [
                    $o->prefix . '-' . $o->order_code,
                    $o->created_at->format('Y-m-d h:i A'),
                    $o->cashier?->name ?? 'Unassigned Cashier',
                    $o->customer?->user?->name ?? 'Walk-in Customer',
                    $o->products->sum('pivot.quantity'),
                    $o->total_amount,
                    $o->tax_amount,
                    $o->payable_amount,
                    str_replace(' Payment', '', $o->payment_method->value ?? $o->payment_method),
                    $o->order_status->value ?? $o->order_status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function formatDuration($seconds)
    {
        if ($seconds <= 0) return '0m';
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        } else {
            return "{$secs}s";
        }
    }
}
