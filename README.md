# LINE Pay Online V4 PHP SDK

Modern, type-safe LINE Pay Online V4 API SDK for PHP.

[繁體中文](./README_ZH.md) | English

## Features

- ✅ PHP 8.1+ with strict types
- ✅ PSR-4 autoloading
- ✅ Builder Pattern for request construction
- ✅ Type-safe enums for currencies and options
- ✅ Comprehensive validation before API calls
- ✅ Full PHPDoc documentation
- ✅ PHPStan Level Max static analysis
- ✅ Depends on `carllee/line-pay-core-v4`

## Requirements

- PHP 8.1 or higher
- Composer
- ext-json
- ext-openssl

## Installation

```bash
composer require carllee/line-pay-online-v4
```

## Quick Start

```php
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Enums\Currency;

// Create configuration
$config = new LinePayConfig(
    channelId: getenv('LINE_PAY_CHANNEL_ID'),
    channelSecret: getenv('LINE_PAY_CHANNEL_SECRET'),
    env: 'sandbox' // or 'production'
);

// Create client
$client = new LinePayClient($config);

// Create a package with products
$package = new PaymentPackage(id: 'PKG-001', amount: 1000);
$package->addProduct(new PaymentProduct(
    name: 'Product Name',
    quantity: 1,
    price: 1000
));

// Request payment using Builder Pattern
$response = $client->payment()
    ->setAmount(1000)
    ->setCurrency(Currency::TWD)
    ->setOrderId('ORDER-' . time())
    ->addPackage($package)
    ->setRedirectUrls(
        'https://example.com/confirm',
        'https://example.com/cancel'
    )
    ->send();

// Get payment URL
$paymentUrl = $response['info']['paymentUrl']['web'];
$transactionId = $response['info']['transactionId'];
```

## API Methods

### Request Payment
```php
$response = $client->payment()
    ->setAmount(1000)
    ->setCurrency(Currency::TWD)
    ->setOrderId('ORDER-001')
    ->addPackage($package)
    ->setRedirectUrls($confirmUrl, $cancelUrl)
    ->send();
```

### Confirm Payment
```php
$response = $client->confirm(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::TWD
);
```

### Capture Payment
```php
$response = $client->capture(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::TWD
);
```

### Void Payment
```php
$response = $client->void('1234567890123456789');
```

### Refund Payment
```php
// Full refund
$response = $client->refund('1234567890123456789');

// Partial refund
$response = $client->refund('1234567890123456789', 500);
```

### Get Payment Details
```php
$response = $client->getDetails(
    transactionIds: ['1234567890123456789'],
    orderIds: ['ORDER-001']
);
```

### Check Payment Status
```php
$response = $client->checkStatus('1234567890123456789');
```

## Error Handling

```php
use LinePay\Core\Errors\LinePayError;
use LinePay\Core\Errors\LinePayTimeoutError;
use LinePay\Core\Errors\LinePayValidationError;

try {
    $response = $client->confirm($transactionId, 1000, Currency::TWD);
} catch (LinePayValidationError $e) {
    // Validation error (before API call)
    echo "Validation Error: " . $e->getMessage();
    echo "Field: " . $e->getField();
} catch (LinePayTimeoutError $e) {
    // Request timeout
    echo "Timeout after " . $e->getTimeout() . " seconds";
} catch (LinePayError $e) {
    // API error
    echo "Error Code: " . $e->getReturnCode();
    echo "Error Message: " . $e->getReturnMessage();
    
    if ($e->isAuthError()) {
        // Authentication error (1xxx)
    } elseif ($e->isPaymentError()) {
        // Payment error (2xxx)
    } elseif ($e->isInternalError()) {
        // Internal error (9xxx)
    }
}
```

## Testing

```bash
composer install
composer test
```

## License

MIT License - see [LICENSE](LICENSE) for details.

## Resources

- [LINE Pay API Documentation](https://pay.line.me/documents/online_v3_en.html)
- [LINE Pay Merchant Center](https://pay.line.me/portal/tw/)
