# LINE Pay Online V4 PHP SDK

現代化、類型安全的 LINE Pay Online V4 API PHP SDK。

繁體中文 | [English](./README.md)

## 功能特色

- ✅ PHP 8.1+ 嚴格類型
- ✅ PSR-4 自動載入
- ✅ Builder Pattern 建構請求
- ✅ 類型安全的枚舉（貨幣、選項）
- ✅ API 呼叫前完整驗證
- ✅ 完整 PHPDoc 文件
- ✅ PHPStan Level Max 靜態分析
- ✅ 依賴 `carllee/line-pay-core-v4`

## 系統需求

- PHP 8.1 或更高版本
- Composer
- ext-json
- ext-openssl

## 安裝

```bash
composer require carllee/line-pay-online-v4
```

## 快速開始

```php
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Enums\Currency;

// 建立設定
$config = new LinePayConfig(
    channelId: getenv('LINE_PAY_CHANNEL_ID'),
    channelSecret: getenv('LINE_PAY_CHANNEL_SECRET'),
    env: 'sandbox' // 或 'production'
);

// 建立客戶端
$client = new LinePayClient($config);

// 建立包含產品的套件
$package = new PaymentPackage(id: 'PKG-001', amount: 1000);
$package->addProduct(new PaymentProduct(
    name: '商品名稱',
    quantity: 1,
    price: 1000
));

// 使用 Builder Pattern 請求付款
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

// 取得付款網址
$paymentUrl = $response['info']['paymentUrl']['web'];
$transactionId = $response['info']['transactionId'];
```

## API 方法

### 請求付款
```php
$response = $client->payment()
    ->setAmount(1000)
    ->setCurrency(Currency::TWD)
    ->setOrderId('ORDER-001')
    ->addPackage($package)
    ->setRedirectUrls($confirmUrl, $cancelUrl)
    ->send();
```

### 確認付款
```php
$response = $client->confirm(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::TWD
);
```

### 請款
```php
$response = $client->capture(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::TWD
);
```

### 取消授權
```php
$response = $client->void('1234567890123456789');
```

### 退款
```php
// 全額退款
$response = $client->refund('1234567890123456789');

// 部分退款
$response = $client->refund('1234567890123456789', 500);
```

### 查詢付款詳情
```php
$response = $client->getDetails(
    transactionIds: ['1234567890123456789'],
    orderIds: ['ORDER-001']
);
```

### 檢查付款狀態
```php
$response = $client->checkStatus('1234567890123456789');
```

## 錯誤處理

```php
use LinePay\Core\Errors\LinePayError;
use LinePay\Core\Errors\LinePayTimeoutError;
use LinePay\Core\Errors\LinePayValidationError;

try {
    $response = $client->confirm($transactionId, 1000, Currency::TWD);
} catch (LinePayValidationError $e) {
    // 驗證錯誤（API 呼叫前）
    echo "驗證錯誤: " . $e->getMessage();
    echo "欄位: " . $e->getField();
} catch (LinePayTimeoutError $e) {
    // 請求逾時
    echo "逾時 " . $e->getTimeout() . " 秒";
} catch (LinePayError $e) {
    // API 錯誤
    echo "錯誤代碼: " . $e->getReturnCode();
    echo "錯誤訊息: " . $e->getReturnMessage();
    
    if ($e->isAuthError()) {
        // 認證錯誤 (1xxx)
    } elseif ($e->isPaymentError()) {
        // 付款錯誤 (2xxx)
    } elseif ($e->isInternalError()) {
        // 內部錯誤 (9xxx)
    }
}
```

## 測試

```bash
composer install
composer test
```

## 授權

MIT 授權 - 詳見 [LICENSE](LICENSE)。

## 資源

- [LINE Pay API 文件](https://pay.line.me/documents/online_v3_en.html)
- [LINE Pay 商家後台](https://pay.line.me/portal/tw/)
