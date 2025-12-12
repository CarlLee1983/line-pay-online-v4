# LINE Pay Online V4 PHP SDK

[![CI](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml/badge.svg)](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/carllee/line-pay-online-v4)](https://packagist.org/packages/carllee/line-pay-online-v4)
[![License](https://img.shields.io/github/license/CarlLee1983/line-pay-online-v4-php)](LICENSE)

**LINE Pay Online API V4 PHP SDK。**
類型安全、嚴格類型的程式庫，提供 **Fluent Builder** 建構複雜的付款請求。原生支援 **Laravel**，具備自動發現和 Facade 功能。

**🌐 Language / 語言 / 言語 / ภาษา:**
[English](./README.md) | [繁體中文](./README_ZH.md) | [日本語](./README_JA.md) | [ภาษาไทย](./README_TH.md)

## 功能特色

- ✅ **PHP 8.1+** 嚴格類型
- ✅ **Laravel 整合** - ServiceProvider、Facade、IoC 支援
- ✅ **Builder Pattern** 建構請求
- ✅ **類型安全枚舉** 貨幣、選項等
- ✅ **完整驗證** API 呼叫前驗證
- ✅ **PHPStan Level Max** 靜態分析
- ✅ 基於 `carllee/line-pay-core-v4`

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

### 標準 PHP 使用

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
    env: 'sandbox'
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
```

## Laravel 整合

本套件支援 **Laravel Package Discovery**。只需透過 composer 安裝，ServiceProvider 和 Facade 將自動註冊。

### 設定

發布設定檔：

```bash
php artisan vendor:publish --tag=linepay-config
```

在 `.env` 中加入：

```env
LINE_PAY_CHANNEL_ID=your-channel-id
LINE_PAY_CHANNEL_SECRET=your-channel-secret
LINE_PAY_ENV=sandbox
LINE_PAY_TIMEOUT=20
```

### 使用依賴注入

```php
namespace App\Http\Controllers;

use LinePay\Online\LinePayClient;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Enums\Currency;

class PaymentController extends Controller
{
    public function __construct(
        private LinePayClient $linePay
    ) {}

    public function createPayment()
    {
        $package = new PaymentPackage(id: 'PKG-001', amount: 1000);
        
        $response = $this->linePay->payment()
            ->setAmount(1000)
            ->setCurrency(Currency::TWD)
            ->setOrderId('ORDER-' . time())
            ->addPackage($package)
            ->setRedirectUrls(
                route('payment.confirm'),
                route('payment.cancel')
            )
            ->send();

        return redirect($response['info']['paymentUrl']['web']);
    }
}
```

### 使用 Facade

```php
use LinePay\Online\Laravel\LinePay;
use LinePay\Online\Enums\Currency;

// 確認付款
$response = LinePay::confirm(
    transactionId: $request->input('transactionId'),
    amount: 1000,
    currency: 'TWD'
);

// 退款
$response = LinePay::refund($transactionId, 500);
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
} catch (LinePayTimeoutError $e) {
    // 請求逾時
    echo "逾時 " . $e->getTimeout() . " 秒";
} catch (LinePayError $e) {
    // API 錯誤
    echo "錯誤代碼: " . $e->getReturnCode();
    echo "錯誤訊息: " . $e->getReturnMessage();
}
```

## 常見問題與疑難排解

### 🚫 重複確認（Error 1198）

每個 `transactionId` 只能確認**一次**。

* 如果用戶刷新成功頁面，您的服務器可能會試圖再次確認。
* **解決方案：** 在呼叫 `$client->confirm()` **之前**檢查本地資料庫的訂單狀態。如果已經是 "PAID"，跳過 API 呼叫。

```php
// 在您的確認回呼處理程式中
$order = Order::where('transaction_id', $transactionId)->first();

if ($order->status === 'PAID') {
    // 已經確認，直接顯示成功頁面
    return redirect()->route('payment.success');
}

// 只有尚未付款時才呼叫 confirm
$response = $client->confirm($transactionId, $order->amount, Currency::TWD);
$order->update(['status' => 'PAID']);
```

### 💰 金額不符（Error 1106）

傳遞給 `confirm()` 的金額必須與請求的金額完全一致。

* **提示：** 不要信任 URL 查詢字串中的金額（如果有的話）。始終使用 `orderId` 從您自己的資料庫取得金額。

```php
// ✗ 錯誤：使用查詢字串中的金額
$amount = $request->input('amount'); // 有風險！

// ✓ 正確：使用資料庫中的金額
$order = Order::findOrFail($orderId);
$amount = $order->amount;
```

### ⏱️ 交易過期

`paymentUrl` 和 `transactionId` 有過期時間（通常 20 分鐘）。如果用戶花費太長時間，確認呼叫將會失敗。

* 儲存過期時間並向用戶顯示倒數計時。
* 優雅地處理過期錯誤，允許用戶重新開始付款。

## 測試

```bash
composer install
composer test
composer analyze
```

## 相關套件

- [`carllee/line-pay-core-v4`](https://github.com/CarlLee1983/line-pay-core-v4-php) - 核心 SDK（依賴）
- [`carllee/line-pay-offline-v4`](https://github.com/CarlLee1983/line-pay-offline-v4-php) - Offline 付款 SDK

## 授權

MIT 授權 - 詳見 [LICENSE](LICENSE)。

## 資源

- [LINE Pay API 文件](https://pay.line.me/documents/online_v3_en.html)
- [LINE Pay 商家後台](https://pay.line.me/portal/tw/)
