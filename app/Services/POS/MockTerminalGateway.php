<?php

namespace App\Services\POS;

use App\Contracts\Payments\PaymentTerminalGateway;
use App\DTOs\TerminalSaleRequest;
use App\DTOs\TerminalPaymentResult;
use App\DTOs\TerminalStatusRequest;
use App\DTOs\TerminalVoidRequest;
use App\DTOs\TerminalRefundRequest;
use App\DTOs\TerminalHealthResult;
use App\Enums\PosPaymentAttemptStatus;

class MockTerminalGateway implements PaymentTerminalGateway
{
    public function initiateSale(TerminalSaleRequest $request): TerminalPaymentResult
    {
        return new TerminalPaymentResult(
            status: PosPaymentAttemptStatus::SUCCESS,
            provider: 'mock',
            providerRequestId: 'MOCK-REQ-' . rand(1000, 9999),
            providerTransactionId: 'MOCK-TX-' . str()->random(10),
            paymentMethod: $request->paymentMethod,
            approvedAmount: $request->amount,
            currency: $request->currency,
            terminalId: $request->terminalId ?: 'MOCK-TERM-001',
            merchantId: 'MOCK-MERCHANT-01',
            rrn: '123456789012',
            approvalCode: '998877',
            maskedCard: '411111XXXXXX1111',
            cardNetwork: 'VISA',
            cardType: 'CREDIT',
            upiReference: $request->paymentMethod === 'upi' ? 'upi@mockbank' : null,
            processedTimestamp: now()->toIso8601String(),
            safeRawResponse: ['mock' => true, 'responseCode' => '00', 'message' => 'Approved']
        );
    }

    public function checkStatus(TerminalStatusRequest $request): TerminalPaymentResult
    {
        return new TerminalPaymentResult(
            status: PosPaymentAttemptStatus::SUCCESS,
            provider: 'mock',
            providerTransactionId: $request->transactionId ?: 'MOCK-TX-DEFAULT',
            approvedAmount: 100.00,
            processedTimestamp: now()->toIso8601String(),
            safeRawResponse: ['status' => 'SUCCESS']
        );
    }

    public function cancel(TerminalStatusRequest $request): TerminalPaymentResult
    {
        return new TerminalPaymentResult(
            status: PosPaymentAttemptStatus::CANCELLED,
            provider: 'mock',
            providerTransactionId: $request->transactionId,
            processedTimestamp: now()->toIso8601String(),
            safeRawResponse: ['status' => 'CANCELLED']
        );
    }

    public function void(TerminalVoidRequest $request): TerminalPaymentResult
    {
        return new TerminalPaymentResult(
            status: PosPaymentAttemptStatus::VOIDED,
            provider: 'mock',
            providerTransactionId: $request->transactionId,
            approvedAmount: $request->originalAmount,
            processedTimestamp: now()->toIso8601String(),
            safeRawResponse: ['status' => 'VOIDED']
        );
    }

    public function refund(TerminalRefundRequest $request): TerminalPaymentResult
    {
        $status = ($request->refundAmount >= $request->originalAmount)
            ? PosPaymentAttemptStatus::REFUNDED
            : PosPaymentAttemptStatus::PARTIALLY_REFUNDED;

        return new TerminalPaymentResult(
            status: $status,
            provider: 'mock',
            providerTransactionId: $request->transactionId,
            approvedAmount: $request->refundAmount,
            processedTimestamp: now()->toIso8601String(),
            safeRawResponse: ['status' => 'REFUNDED', 'refund_amount' => $request->refundAmount]
        );
    }

    public function healthCheck(): TerminalHealthResult
    {
        return new TerminalHealthResult(
            isHealthy: true,
            provider: 'mock',
            terminalId: 'MOCK-TERM-001',
            batteryLevel: 98,
            isOnline: true,
            responseTimeMs: 15,
            details: ['bridge' => 'connected']
        );
    }
}
