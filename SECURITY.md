# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take security issues seriously. If you discover a security vulnerability, please report it responsibly.

### How to Report

1. **DO NOT** create a public GitHub issue for security vulnerabilities
2. Email your report to: carllee1983@gmail.com
3. Include as much detail as possible:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

### What to Expect

- **Response Time**: We aim to acknowledge your report within 48 hours
- **Updates**: We will keep you informed of our progress
- **Resolution**: We will notify you when the issue is fixed
- **Credit**: We will credit you in the release notes (unless you prefer anonymity)

## Security Best Practices

When using this SDK, follow these security practices:

### 1. Protect Your Credentials

```php
// ✅ Use environment variables
$config = new LinePayConfig(
    channelId: getenv('LINE_PAY_CHANNEL_ID'),
    channelSecret: getenv('LINE_PAY_CHANNEL_SECRET')
);

// ❌ Never hardcode credentials
$config = new LinePayConfig(
    channelId: '1234567890',
    channelSecret: 'your-secret-key'
);
```

### 2. Use HTTPS

Always use HTTPS for your confirm and cancel URLs:

```php
$response = $client->payment()
    ->setRedirectUrls(
        'https://example.com/confirm',  // ✅ HTTPS
        'https://example.com/cancel'    // ✅ HTTPS
    )
    ->send();
```

### 3. Validate Transaction IDs

The SDK automatically validates transaction IDs, but always verify the transaction belongs to your merchant:

```php
// Verify the transaction exists in your database
$order = Order::findByTransactionId($transactionId);
if (!$order) {
    throw new Exception('Unknown transaction');
}
```

### 4. Verify Signatures

Use the built-in signature verification for callbacks:

```php
use LinePay\Core\LinePayUtils;

$isValid = LinePayUtils::verifySignature(
    $channelSecret,
    $requestUri,
    $requestBody,
    $nonce,
    $receivedSignature
);

if (!$isValid) {
    throw new Exception('Invalid signature');
}
```

### 5. Handle Errors Securely

Don't expose internal error details to end users:

```php
try {
    $response = $client->confirm($transactionId, $amount, $currency);
} catch (LinePayError $e) {
    // Log the full error internally
    error_log($e->getMessage());
    
    // Show generic message to user
    return response()->json([
        'error' => 'Payment processing failed'
    ], 400);
}
```

## Dependencies

This SDK depends on:
- `carllee/line-pay-core-v4`: Core functionality
- `guzzlehttp/guzzle`: HTTP client

We regularly update dependencies to address security vulnerabilities.

## Security Updates

Security fixes are released as patch versions and announced in:
- GitHub Release notes
- CHANGELOG.md
