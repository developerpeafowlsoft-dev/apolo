<?php

namespace App\Services\POS;

use App\Models\POSShift;
use App\Models\Order;
use App\Models\POSReturn;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class POSShiftService
{
    /**
     * Get the active open shift for a cashier at a specific counter.
     */
    public function getActiveShift($shopId, $counterId, $userId)
    {
        return POSShift::where('shop_id', $shopId)
            ->where('counter_id', $counterId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Start a new shift session.
     */
    public function openShift($shopId, $counterId, $userId, $openingCash): POSShift
    {
        // Check if there is already an open shift
        $existing = $this->getActiveShift($shopId, $counterId, $userId);
        if ($existing) {
            throw new Exception("There is already an active shift open on this counter. Please close it first.");
        }

        return POSShift::create([
            'shop_id' => $shopId,
            'counter_id' => $counterId,
            'user_id' => $userId,
            'opening_cash' => (float)$openingCash,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    /**
     * Calculate sales totals and returns processed during the active shift.
     */
    public function getShiftTransactions(POSShift $shift): array
    {
        $start = $shift->opened_at;
        $end = now();

        // 1. Calculate Sales
        // Cash Sales from payments table associated with POS orders
        $cashSales = DB::table('payments')
            ->join('order_payments', 'payments.id', '=', 'order_payments.payment_id')
            ->join('orders', 'order_payments.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shift->shop_id)
            ->where('orders.counter_id', $shift->counter_id)
            // If the order has multiple payments or single payment
            ->where('orders.created_at', '>=', $start)
            ->where('orders.created_at', '<=', $end)
            ->where('payments.payment_method', \App\Enums\PaymentMethod::CASH->value)
            ->sum('payments.amount');

        $cardSales = DB::table('payments')
            ->join('order_payments', 'payments.id', '=', 'order_payments.payment_id')
            ->join('orders', 'order_payments.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shift->shop_id)
            ->where('orders.counter_id', $shift->counter_id)
            ->where('orders.created_at', '>=', $start)
            ->where('orders.created_at', '<=', $end)
            ->where('payments.payment_method', \App\Enums\PaymentMethod::ONLINE->value)
            ->sum('payments.amount');

        // 2. Calculate Returns
        $cashReturns = POSReturn::where('shop_id', $shift->shop_id)
            ->where('counter_id', $shift->counter_id)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $cardReturns = POSReturn::where('shop_id', $shift->shop_id)
            ->where('counter_id', $shift->counter_id)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->where('payment_method', 'online')
            ->sum('total_amount');

        $expectedCash = $shift->opening_cash + $cashSales - $cashReturns;

        return [
            'cash_sales' => (float)$cashSales,
            'card_sales' => (float)$cardSales,
            'cash_returns' => (float)$cashReturns,
            'card_returns' => (float)$cardReturns,
            'expected_cash' => (float)$expectedCash,
        ];
    }

    /**
     * Close the register shift session and record discrepancy stats.
     */
    public function closeShift(POSShift $shift, $closingCash, $note = null): POSShift
    {
        return DB::transaction(function () use ($shift, $closingCash, $note) {
            // Re-fetch shift and lock it
            $lockedShift = POSShift::where('id', $shift->id)->lockForUpdate()->first();
            if ($lockedShift->status === 'closed') {
                throw new Exception("This register shift has already been closed.");
            }

            $stats = $this->getShiftTransactions($lockedShift);
            $difference = (float)$closingCash - $stats['expected_cash'];

            $lockedShift->update([
                'closing_cash' => (float)$closingCash,
                'expected_cash' => $stats['expected_cash'],
                'total_sales_cash' => $stats['cash_sales'],
                'total_sales_card' => $stats['card_sales'],
                'total_returns_cash' => $stats['cash_returns'],
                'total_returns_card' => $stats['card_returns'],
                'status' => 'closed',
                'closed_at' => now(),
                'difference' => $difference,
                'note' => $note,
            ]);

            return $lockedShift;
        });
    }
}
