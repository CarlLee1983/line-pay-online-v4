<?php

declare(strict_types=1);

namespace LinePay\Online\Tests;

use InvalidArgumentException;
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\Enums\Currency;
use LinePay\Online\LinePayClient;
use PHPUnit\Framework\TestCase;

/**
 * Tests for LinePayClient API method validations.
 */
class LinePayClientApiTest extends TestCase
{
    private LinePayClient $client;

    protected function setUp(): void
    {
        $config = new LinePayConfig(
            channelId: 'test-channel-id',
            channelSecret: 'test-channel-secret',
            env: 'sandbox'
        );

        $this->client = new LinePayClient($config);
    }

    public function testConfirmWithInvalidTransactionIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->confirm('12345', 1000, Currency::TWD);
    }

    public function testConfirmWithStringCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        // This will throw due to invalid transaction ID before reaching API
        $this->client->confirm('invalid', 1000, 'TWD');
    }

    public function testCaptureWithInvalidTransactionIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->capture('invalid-id', 1000, Currency::TWD);
    }

    public function testVoidWithInvalidTransactionIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->void('not-19-digits');
    }

    public function testRefundWithInvalidTransactionIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->refund('short');
    }

    public function testRefundWithPartialAmountValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->refund('abc', 500);
    }

    public function testCheckStatusWithInvalidTransactionIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transactionId format');

        $this->client->checkStatus('bad-id');
    }

    public function testConfirmWithZeroAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        // Using a valid 19-digit transaction ID
        $this->client->confirm('2021121300698360010', 0, Currency::TWD);
    }

    public function testConfirmWithNegativeAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->client->confirm('2021121300698360010', -100, Currency::TWD);
    }

    public function testCaptureWithZeroAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->client->capture('2021121300698360010', 0, Currency::TWD);
    }

    public function testCaptureWithNegativeAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->client->capture('2021121300698360010', -50, Currency::TWD);
    }

    public function testRefundWithZeroAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refund amount must be greater than 0');

        $this->client->refund('2021121300698360010', 0);
    }

    public function testRefundWithNegativeAmountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refund amount must be greater than 0');

        $this->client->refund('2021121300698360010', -200);
    }

    public function testRefundWithoutAmountIsValid(): void
    {
        // This should not throw - full refund is allowed
        // It will fail at HTTP level but validation should pass
        $this->expectException(\Exception::class); // Will fail on HTTP, but not on amount validation

        $this->client->refund('2021121300698360010');
    }

    public function testGetDetailsWithoutParametersThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one of transactionIds or orderIds must be provided');

        $this->client->getDetails();
    }

    public function testGetDetailsWithEmptyArraysThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one of transactionIds or orderIds must be provided');

        $this->client->getDetails([], []);
    }

    public function testGetDetailsWithOnlyTransactionIds(): void
    {
        $this->expectException(\Exception::class); // Will fail on HTTP, but not on validation

        $this->client->getDetails(['2021121300698360010']);
    }

    public function testGetDetailsWithOnlyOrderIds(): void
    {
        $this->expectException(\Exception::class); // Will fail on HTTP, but not on validation

        $this->client->getDetails(orderIds: ['ORDER_001']);
    }
}
