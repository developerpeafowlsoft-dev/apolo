<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\DTOs\TerminalSaleRequest;
use App\DTOs\TerminalPaymentResult;
use App\DTOs\TerminalRefundRequest;
use App\DTOs\TerminalVoidRequest;
use App\DTOs\TerminalStatusRequest;
use App\DTOs\TerminalHealthResult;
use App\Enums\PaymentTerminalProvider;
use App\Enums\PosPaymentAttemptStatus;
use App\Services\POS\TerminalGatewayFactory;
use App\Services\POS\MockTerminalGateway;
use App\Exceptions\ProviderNotConfiguredException;
use InvalidArgumentException;

class TerminalDomainLayerTest extends TestCase
{
    public function test_dto_validation_throws_on_invalid_inputs()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Sale amount must be positive.");

        new TerminalSaleRequest(
            attemptUuid: 'uuid-123',
            billReference: 'BILL-001',
            provider: 'mock',
            paymentMethod: 'card',
            amount: -50.00
        );
    }

    public function test_refund_dto_validation_prevents_excess_refund()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Refund amount cannot exceed original amount.");

        new TerminalRefundRequest(
            attemptUuid: 'uuid-123',
            transactionId: 'TX-100',
            refundAmount: 600.00,
            originalAmount: 500.00
        );
    }

    public function test_factory_resolves_mock_gateway()
    {
        $factory = new TerminalGatewayFactory();
        $gateway = $factory->make(PaymentTerminalProvider::MOCK);

        $this->assertInstanceOf(MockTerminalGateway::class, $gateway);
    }

    public function test_factory_resolves_gateway_from_string()
    {
        $factory = new TerminalGatewayFactory();
        $gateway = $factory->make('mock');

        $this->assertInstanceOf(MockTerminalGateway::class, $gateway);
    }

    public function test_factory_throws_exception_on_unsupported_provider()
    {
        $this->expectException(ProviderNotConfiguredException::class);

        $factory = new TerminalGatewayFactory();
        $factory->make('invalid_unsupported_provider');
    }

    public function test_status_normalization_converts_string_to_enum()
    {
        $result = new TerminalPaymentResult(
            status: 'success',
            provider: 'mock',
            approvedAmount: 100.00
        );

        $this->assertInstanceOf(PosPaymentAttemptStatus::class, $result->normalizedStatus);
        $this->assertEquals(PosPaymentAttemptStatus::SUCCESS, $result->normalizedStatus);
        $this->assertTrue($result->isSuccess());
    }

    public function test_amount_normalization_throws_on_negative_approved_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Approved amount cannot be negative.");

        new TerminalPaymentResult(
            status: PosPaymentAttemptStatus::SUCCESS,
            provider: 'mock',
            approvedAmount: -100.00
        );
    }
}
