<?php

declare(strict_types=1);

namespace LinePay\Online\Tests;

use LinePay\Online\Domain\PaymentOptions;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Domain\RedirectUrls;
use LinePay\Online\Enums\PayType;
use PHPUnit\Framework\TestCase;

class DomainTest extends TestCase
{
    public function testPaymentProductToArray(): void
    {
        $product = new PaymentProduct(
            name: 'Test Product',
            quantity: 2,
            price: 100,
            id: 'PROD-001',
            imageUrl: 'https://example.com/image.jpg',
            originalPrice: 120
        );

        $array = $product->toArray();

        $this->assertEquals('Test Product', $array['name']);
        $this->assertEquals(2, $array['quantity']);
        $this->assertEquals(100, $array['price']);
        $this->assertEquals('PROD-001', $array['id']);
        $this->assertEquals('https://example.com/image.jpg', $array['imageUrl']);
        $this->assertEquals(120, $array['originalPrice']);
    }

    public function testPaymentProductMinimal(): void
    {
        $product = new PaymentProduct(
            name: 'Simple Product',
            quantity: 1,
            price: 50
        );

        $array = $product->toArray();

        $this->assertEquals('Simple Product', $array['name']);
        $this->assertEquals(1, $array['quantity']);
        $this->assertEquals(50, $array['price']);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('imageUrl', $array);
        $this->assertArrayNotHasKey('originalPrice', $array);
    }

    public function testPaymentPackageToArray(): void
    {
        $package = new PaymentPackage(
            id: 'PKG-001',
            amount: 200,
            name: 'Test Package',
            userFee: 10
        );

        $product1 = new PaymentProduct('Product 1', 1, 100);
        $product2 = new PaymentProduct('Product 2', 1, 100);

        $package->addProduct($product1)->addProduct($product2);

        $array = $package->toArray();

        $this->assertEquals('PKG-001', $array['id']);
        $this->assertEquals(200, $array['amount']);
        $this->assertEquals('Test Package', $array['name']);
        $this->assertEquals(10, $array['userFee']);
        $this->assertIsArray($array['products']);
        $this->assertCount(2, (array) $array['products']);
    }

    public function testPaymentPackageGetProducts(): void
    {
        $package = new PaymentPackage('PKG-001', 100);
        $product = new PaymentProduct('Product', 1, 100);

        $package->addProduct($product);

        $products = $package->getProducts();

        $this->assertCount(1, $products);
        $this->assertSame($product, $products[0]);
    }

    public function testRedirectUrlsToArray(): void
    {
        $urls = new RedirectUrls(
            confirmUrl: 'https://example.com/confirm',
            cancelUrl: 'https://example.com/cancel'
        );

        $array = $urls->toArray();

        $this->assertEquals('https://example.com/confirm', $array['confirmUrl']);
        $this->assertEquals('https://example.com/cancel', $array['cancelUrl']);
    }

    public function testPaymentOptionsToArray(): void
    {
        $options = new PaymentOptions(
            capture: false,
            payType: PayType::PREAPPROVED,
            locale: 'zh-Hant',
            checkConfirmUrlBrowser: true,
            branchName: 'Main Branch',
            branchId: 'BRANCH-001'
        );

        $array = $options->toArray();

        $this->assertIsArray($array['payment']);
        $this->assertIsArray($array['display']);
        $this->assertIsArray($array['extra']);

        /** @var array<string, mixed> $payment */
        $payment = $array['payment'];
        /** @var array<string, mixed> $display */
        $display = $array['display'];
        /** @var array<string, mixed> $extra */
        $extra = $array['extra'];

        $this->assertFalse($payment['capture']);
        $this->assertEquals('PREAPPROVED', $payment['payType']);
        $this->assertEquals('zh-Hant', $display['locale']);
        $this->assertTrue($display['checkConfirmUrlBrowser']);
        $this->assertEquals('Main Branch', $extra['branchName']);
        $this->assertEquals('BRANCH-001', $extra['branchId']);
    }

    public function testPaymentOptionsEmpty(): void
    {
        $options = new PaymentOptions();
        $array = $options->toArray();

        $this->assertEmpty($array);
    }
}
