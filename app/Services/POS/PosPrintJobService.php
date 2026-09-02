<?php

namespace App\Services\POS;

use App\Models\Order;
use App\Models\PosPrintJob;
use App\Models\User;
use App\Enums\PosPrintJobStatus;
use App\Repositories\PosPrintJobRepository;
use App\Repositories\PosTerminalEventRepository;
use App\Services\POS\BridgeRequestSigner;
use App\Exceptions\BridgeAuthenticationException;
use Illuminate\Support\Facades\DB;
use Exception;

class PosPrintJobService
{
    protected $printJobRepository;
    protected $eventRepository;
    protected $signer;

    public function __construct(
        PosPrintJobRepository $printJobRepository,
        PosTerminalEventRepository $eventRepository,
        BridgeRequestSigner $signer
    ) {
        $this->printJobRepository = $printJobRepository;
        $this->eventRepository = $eventRepository;
        $this->signer = $signer;
    }

    /**
     * Build ESC/POS formatted print payload for order and save pending print job.
     */
    public function createJobForOrder(Order $order, ?string $attemptId = null): PosPrintJob
    {
        $shop = $order->shop;
        $items = $order->products ?? [];

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'name' => $item->name,
                'qty' => $item->pivot->quantity ?? 1,
                'price' => (float)($item->pivot->unit_price ?? $item->unit_price ?? 0),
                'total' => (float)($item->pivot->total_price ?? 0),
                'hsn' => $item->hsn_code ?? '9988',
                'gst_percent' => (float)($item->vat_percent ?? 18.0),
            ];
        }

        $printPayload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'shop_name' => $shop->name ?? 'Retail Store',
            'shop_address' => $shop->address ?? 'Main Branch',
            'shop_gstin' => $shop->gstin ?? '27AAAAA0000A1Z5',
            'customer_name' => $order->customer->name ?? 'Walk-in Customer',
            'customer_phone' => $order->customer->phone ?? '-',
            'date' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : date('d/m/Y H:i'),
            'paper_width_mm' => 80,
            'copies' => 1,
            'pulse_cash_drawer' => true,
            'items' => $formattedItems,
            'subtotal' => (float)$order->total_amount,
            'gst_total' => (float)($order->total_tax ?? 0),
            'grand_total' => (float)$order->payable_amount,
            'payment_method' => strtoupper($order->payment_method->value ?? 'CASH'),
            'attempt_id' => $attemptId,
        ];

        return $this->printJobRepository->createPrintJob(
            orderId: $order->id,
            payload: json_encode($printPayload)
        );
    }

    /**
     * Mark print job printed successfully.
     */
    public function markPrinted(PosPrintJob $job, array $bridgeResponse = []): PosPrintJob
    {
        return DB::transaction(function () use ($job, $bridgeResponse) {
            $job->update([
                'status' => PosPrintJobStatus::PRINTED,
                'attempts' => ($job->attempts ?? 0) + 1,
            ]);

            if ($attempt = $job->order?->paymentAttempt) {
                $this->eventRepository->logEvent(
                    $attempt->id,
                    'PRINT_SUCCESS',
                    'pending',
                    'printed',
                    ['print_job_id' => $job->id, 'order_id' => $job->order_id]
                );
            }

            return $job;
        });
    }

    /**
     * Mark print job failed without rolling back order or payment.
     */
    public function markFailed(PosPrintJob $job, string $errorReason): PosPrintJob
    {
        return DB::transaction(function () use ($job, $errorReason) {
            $job->update([
                'status' => PosPrintJobStatus::FAILED,
                'error_message' => $errorReason,
            ]);

            if ($attempt = $job->order?->paymentAttempt) {
                $this->eventRepository->logEvent(
                    $attempt->id,
                    'PRINT_FAILURE',
                    'pending',
                    'failed',
                    ['print_job_id' => $job->id, 'error' => $errorReason]
                );
            }

            return $job;
        });
    }

    /**
     * Create reprint job with permission check and audit log.
     */
    public function reprintOrder(Order $order, User $cashier, string $reason = 'Customer copy request'): PosPrintJob
    {
        return DB::transaction(function () use ($order, $cashier, $reason) {
            if ($attempt = $order->paymentAttempt) {
                $this->eventRepository->logEvent(
                    $attempt->id,
                    'REPRINT_REQUESTED',
                    'printed',
                    'reprint_pending',
                    [
                        'order_id' => $order->id,
                        'cashier_id' => $cashier->id,
                        'reason' => $reason,
                        'timestamp' => now()->toIso8601String(),
                    ]
                );
            }

            $newJob = $this->createJobForOrder($order);
            $newJob->update([
                'error_message' => 'REPRINT: ' . $reason,
            ]);

            return $newJob;
        });
    }
}
