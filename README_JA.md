# LINE Pay Online V4 PHP SDK

[![CI](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml/badge.svg)](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/carllee/line-pay-online-v4)](https://packagist.org/packages/carllee/line-pay-online-v4)
[![License](https://img.shields.io/github/license/CarlLee1983/line-pay-online-v4-php)](LICENSE)

モダンでタイプセーフな LINE Pay Online V4 API PHP SDK。Laravel対応。

**🌐 Language / 語言 / 言語 / ภาษา:**
[English](./README.md) | [繁體中文](./README_ZH.md) | [日本語](./README_JA.md) | [ภาษาไทย](./README_TH.md)

## 機能

- ✅ **PHP 8.1+** 厳格な型
- ✅ **Laravel統合** - ServiceProvider、Facade、IoC対応
- ✅ **Builderパターン** リクエスト構築
- ✅ **タイプセーフなEnum** 通貨、オプション等
- ✅ **完全なバリデーション** API呼び出し前
- ✅ **PHPStan Level Max** 静的解析
- ✅ `carllee/line-pay-core-v4` ベース

## 要件

- PHP 8.1以上
- Composer
- ext-json
- ext-openssl

## インストール

```bash
composer require carllee/line-pay-online-v4
```

## クイックスタート

### 標準PHP使用

```php
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Enums\Currency;

// 設定を作成
$config = new LinePayConfig(
    channelId: getenv('LINE_PAY_CHANNEL_ID'),
    channelSecret: getenv('LINE_PAY_CHANNEL_SECRET'),
    env: 'sandbox'
);

// クライアントを作成
$client = new LinePayClient($config);

// 商品を含むパッケージを作成
$package = new PaymentPackage(id: 'PKG-001', amount: 1000);
$package->addProduct(new PaymentProduct(
    name: '商品名',
    quantity: 1,
    price: 1000
));

// Builderパターンで決済をリクエスト
$response = $client->payment()
    ->setAmount(1000)
    ->setCurrency(Currency::JPY)
    ->setOrderId('ORDER-' . time())
    ->addPackage($package)
    ->setRedirectUrls(
        'https://example.com/confirm',
        'https://example.com/cancel'
    )
    ->send();

// 決済URLを取得
$paymentUrl = $response['info']['paymentUrl']['web'];
```

## Laravel統合

### 設定

設定ファイルを公開：

```bash
php artisan vendor:publish --tag=linepay-config
```

`.env` に追加：

```env
LINE_PAY_CHANNEL_ID=your-channel-id
LINE_PAY_CHANNEL_SECRET=your-channel-secret
LINE_PAY_ENV=sandbox
LINE_PAY_TIMEOUT=20
```

### 依存性注入を使用

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
            ->setCurrency(Currency::JPY)
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

### Facadeを使用

```php
use LinePay\Online\Laravel\LinePay;

// 決済を確認
$response = LinePay::confirm(
    transactionId: $request->input('transactionId'),
    amount: 1000,
    currency: 'JPY'
);

// 返金
$response = LinePay::refund($transactionId, 500);
```

## APIメソッド

### 決済リクエスト
```php
$response = $client->payment()
    ->setAmount(1000)
    ->setCurrency(Currency::JPY)
    ->setOrderId('ORDER-001')
    ->addPackage($package)
    ->setRedirectUrls($confirmUrl, $cancelUrl)
    ->send();
```

### 決済確認
```php
$response = $client->confirm(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::JPY
);
```

### キャプチャ
```php
$response = $client->capture(
    transactionId: '1234567890123456789',
    amount: 1000,
    currency: Currency::JPY
);
```

### 取消
```php
$response = $client->void('1234567890123456789');
```

### 返金
```php
// 全額返金
$response = $client->refund('1234567890123456789');

// 一部返金
$response = $client->refund('1234567890123456789', 500);
```

### 決済詳細取得
```php
$response = $client->getDetails(
    transactionIds: ['1234567890123456789'],
    orderIds: ['ORDER-001']
);
```

### 決済ステータス確認
```php
$response = $client->checkStatus('1234567890123456789');
```

## エラーハンドリング

```php
use LinePay\Core\Errors\LinePayError;
use LinePay\Core\Errors\LinePayTimeoutError;
use LinePay\Core\Errors\LinePayValidationError;

try {
    $response = $client->confirm($transactionId, 1000, Currency::JPY);
} catch (LinePayValidationError $e) {
    // バリデーションエラー（API呼び出し前）
    echo "バリデーションエラー: " . $e->getMessage();
} catch (LinePayTimeoutError $e) {
    // タイムアウト
    echo "タイムアウト " . $e->getTimeout() . " 秒";
} catch (LinePayError $e) {
    // APIエラー
    echo "エラーコード: " . $e->getReturnCode();
    echo "エラーメッセージ: " . $e->getReturnMessage();
}
```

## テスト

```bash
composer install
composer test
composer analyze
```

## 関連パッケージ

- [`carllee/line-pay-core-v4`](https://github.com/CarlLee1983/line-pay-core-v4-php) - コアSDK（依存）
- [`carllee/line-pay-offline-v4`](https://github.com/CarlLee1983/line-pay-offline-v4-php) - オフライン決済SDK

## ライセンス

MIT License - 詳細は [LICENSE](LICENSE) を参照。

## リソース

- [LINE Pay APIドキュメント](https://pay.line.me/documents/online_v3_en.html)
- [LINE Pay加盟店センター](https://pay.line.me/portal/jp/)
