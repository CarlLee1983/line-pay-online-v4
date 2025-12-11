<?php

declare(strict_types=1);

namespace LinePay\Online\Tests;

use LinePay\Core\Config\LinePayConfig;
use LinePay\Core\Errors\LinePayValidationError;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Enums\Currency;
use LinePay\Online\LinePayClient;
use LinePay\Online\Payments\RequestPayment;
use PHPUnit\Framework\TestCase;

class RequestPaymentTest extends TestCase
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

    public function testBuilderPattern(): void
    {
        $package = new PaymentPackage('PKG-001', 100);
        $package->addProduct(new PaymentProduct('Product', 1, 100));

        $builder = $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->addPackage($package)
            ->setRedirectUrls('https://example.com/confirm', 'https://example.com/cancel');

        $this->assertInstanceOf(RequestPayment::class, $builder);
    }

    public function testToBody(): void
    {
        $package = new PaymentPackage('PKG-001', 100);
        $package->addProduct(new PaymentProduct('Product', 1, 100));

        $body = $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->addPackage($package)
            ->setRedirectUrls('https://example.com/confirm', 'https://example.com/cancel')
            ->toBody();

        $this->assertEquals(100, $body['amount']);
        $this->assertEquals('TWD', $body['currency']);
        $this->assertEquals('ORDER-001', $body['orderId']);
        $this->assertIsArray($body['packages']);
        $this->assertCount(1, (array) $body['packages']);
        $this->assertIsArray($body['redirectUrls']);
        /** @var array<string, string> $redirectUrls */
        $redirectUrls = $body['redirectUrls'];
        $this->assertEquals('https://example.com/confirm', $redirectUrls['confirmUrl']);
    }

    public function testValidateRequiresAmount(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('Amount is required');

        $this->client->payment()
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->validate();
    }

    public function testValidateRequiresCurrency(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('Currency is required');

        $this->client->payment()
            ->setAmount(100)
            ->setOrderId('ORDER-001')
            ->validate();
    }

    public function testValidateRequiresOrderId(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('OrderId is required');

        $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->validate();
    }

    public function testValidateRequiresPackages(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('At least one package is required');

        $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->validate();
    }

    public function testValidateRequiresRedirectUrls(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('Redirect URLs are required');

        $package = new PaymentPackage('PKG-001', 100);
        $package->addProduct(new PaymentProduct('Product', 1, 100));

        $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->addPackage($package)
            ->validate();
    }

    public function testValidatePackageAmountMismatch(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('Sum of package amounts');

        $package = new PaymentPackage('PKG-001', 50);
        $package->addProduct(new PaymentProduct('Product', 1, 50));

        $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->addPackage($package)
            ->setRedirectUrls('https://example.com/confirm', 'https://example.com/cancel')
            ->validate();
    }

    public function testValidateProductAmountMismatch(): void
    {
        $this->expectException(LinePayValidationError::class);
        $this->expectExceptionMessage('Sum of product amounts');

        $package = new PaymentPackage('PKG-001', 100);
        $package->addProduct(new PaymentProduct('Product', 1, 50));

        $this->client->payment()
            ->setAmount(100)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-001')
            ->addPackage($package)
            ->setRedirectUrls('https://example.com/confirm', 'https://example.com/cancel')
            ->validate();
    }
}
