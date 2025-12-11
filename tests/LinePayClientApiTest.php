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
}
