<?php

declare(strict_types=1);

namespace LinePay\Online\Tests;

use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;
use LinePay\Online\Payments\RequestPayment;
use PHPUnit\Framework\TestCase;

class LinePayClientTest extends TestCase
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

    public function testClientCreation(): void
    {
        $this->assertEquals('test-channel-id', $this->client->getChannelId());
        $this->assertEquals('https://sandbox-api-pay.line.me', $this->client->getBaseUrl());
    }

    public function testPaymentReturnsRequestPaymentBuilder(): void
    {
        $builder = $this->client->payment();

        $this->assertInstanceOf(RequestPayment::class, $builder);
    }

    public function testProductionEnvironment(): void
    {
        $config = new LinePayConfig(
            channelId: 'prod-id',
            channelSecret: 'prod-secret',
            env: 'production'
        );

        $client = new LinePayClient($config);

        $this->assertEquals('https://api-pay.line.me', $client->getBaseUrl());
    }
}
