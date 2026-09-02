<?php

namespace App\Contracts\Payments;

use App\DTOs\TerminalSaleRequest;
use App\DTOs\TerminalPaymentResult;
use App\DTOs\TerminalStatusRequest;
use App\DTOs\TerminalVoidRequest;
use App\DTOs\TerminalRefundRequest;
use App\DTOs\TerminalHealthResult;

interface PaymentTerminalGateway
{
    public function initiateSale(TerminalSaleRequest $request): TerminalPaymentResult;

    public function checkStatus(TerminalStatusRequest $request): TerminalPaymentResult;

    public function cancel(TerminalStatusRequest $request): TerminalPaymentResult;

    public function void(TerminalVoidRequest $request): TerminalPaymentResult;

    public function refund(TerminalRefundRequest $request): TerminalPaymentResult;

    public function healthCheck(): TerminalHealthResult;
}
