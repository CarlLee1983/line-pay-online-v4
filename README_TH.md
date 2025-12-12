# LINE Pay Online V4 PHP SDK

[![CI](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml/badge.svg)](https://github.com/CarlLee1983/line-pay-online-v4-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/carllee/line-pay-online-v4)](https://packagist.org/packages/carllee/line-pay-online-v4)
[![License](https://img.shields.io/github/license/CarlLee1983/line-pay-online-v4-php)](LICENSE)

**LINE Pay Online API V4 PHP SDK**
ไลบรารีที่ปลอดภัยด้านประเภทข้อมูลและกำหนดประเภทอย่างเข้มงวด มาพร้อม **Fluent Builder** สำหรับสร้างคำขอชำระเงินที่ซับซ้อน รองรับ **Laravel** แบบ native พร้อม auto-discovery และ Facades

**🌐 Language / 語言 / 言語 / ภาษา:**
[English](./README.md) | [繁體中文](./README_ZH.md) | [日本語](./README_JA.md) | [ภาษาไทย](./README_TH.md)

## คุณสมบัติ

- ✅ **PHP 8.1+** พร้อม strict types
- ✅ **Laravel Integration** - ServiceProvider, Facade, IoC support
- ✅ **Builder Pattern** สำหรับสร้าง request
- ✅ **Type-Safe Enums** สกุลเงิน, ตัวเลือก ฯลฯ
- ✅ **การตรวจสอบที่ครบถ้วน** ก่อนเรียก API
- ✅ **PHPStan Level Max** การวิเคราะห์แบบ static
- ✅ สร้างบน `carllee/line-pay-core-v4`

## ความต้องการ

- PHP 8.1 หรือสูงกว่า
- Composer
- ext-json
- ext-openssl

## การติดตั้ง

```bash
composer require carllee/line-pay-online-v4
```

## เริ่มต้นใช้งาน

### การใช้งาน PHP มาตรฐาน

```php
use LinePay\Core\Config\LinePayConfig;
use LinePay\Online\LinePayClient;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\PaymentProduct;
use LinePay\Online\Enums\Currency;

// สร้างการตั้งค่า
$config = new LinePayConfig(
    channelId: getenv('LINE_PAY_CHANNEL_ID'),
    channelSecret: getenv('LINE_PAY_CHANNEL_SECRET'),
    env: 'sandbox'
);

// สร้าง client
$client = new LinePayClient($config);

// สร้าง package พร้อมสินค้า
$package = new PaymentPackage(id: 'PKG-001', amount: 100);
$package->addProduct(new PaymentProduct(
    name: 'ชื่อสินค้า',
    quantity: 1,
    price: 100
));

// ขอชำระเงินด้วย Builder Pattern
$response = $client->payment()
    ->setAmount(100)
    ->setCurrency(Currency::THB)
    ->setOrderId('ORDER-' . time())
    ->addPackage($package)
    ->setRedirectUrls(
        'https://example.com/confirm',
        'https://example.com/cancel'
    )
    ->send();

// รับ URL ชำระเงิน
$paymentUrl = $response['info']['paymentUrl']['web'];
```

## การใช้งานกับ Laravel

แพ็คเกจนี้รองรับ **Laravel Package Discovery** เพียงติดตั้งผ่าน composer แล้ว ServiceProvider และ Facade จะลงทะเบียนโดยอัตโนมัติ

### การตั้งค่า

เผยแพร่ไฟล์ config:

```bash
php artisan vendor:publish --tag=linepay-config
```

เพิ่มใน `.env`:

```env
LINE_PAY_CHANNEL_ID=your-channel-id
LINE_PAY_CHANNEL_SECRET=your-channel-secret
LINE_PAY_ENV=sandbox
LINE_PAY_TIMEOUT=20
```

### การใช้ Dependency Injection

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
        $package = new PaymentPackage(id: 'PKG-001', amount: 100);
        
        $response = $this->linePay->payment()
            ->setAmount(100)
            ->setCurrency(Currency::THB)
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

### การใช้ Facade

```php
use LinePay\Online\Laravel\LinePay;

// ยืนยันการชำระเงิน
$response = LinePay::confirm(
    transactionId: $request->input('transactionId'),
    amount: 100,
    currency: 'THB'
);

// คืนเงิน
$response = LinePay::refund($transactionId, 50);
```

## API Methods

### ขอชำระเงิน
```php
$response = $client->payment()
    ->setAmount(100)
    ->setCurrency(Currency::THB)
    ->setOrderId('ORDER-001')
    ->addPackage($package)
    ->setRedirectUrls($confirmUrl, $cancelUrl)
    ->send();
```

### ยืนยันการชำระเงิน
```php
$response = $client->confirm(
    transactionId: '1234567890123456789',
    amount: 100,
    currency: Currency::THB
);
```

### Capture
```php
$response = $client->capture(
    transactionId: '1234567890123456789',
    amount: 100,
    currency: Currency::THB
);
```

### ยกเลิก
```php
$response = $client->void('1234567890123456789');
```

### คืนเงิน
```php
// คืนเงินเต็มจำนวน
$response = $client->refund('1234567890123456789');

// คืนเงินบางส่วน
$response = $client->refund('1234567890123456789', 50);
```

### ดึงรายละเอียดการชำระเงิน
```php
$response = $client->getDetails(
    transactionIds: ['1234567890123456789'],
    orderIds: ['ORDER-001']
);
```

### ตรวจสอบสถานะการชำระเงิน
```php
$response = $client->checkStatus('1234567890123456789');
```

## การจัดการข้อผิดพลาด

```php
use LinePay\Core\Errors\LinePayError;
use LinePay\Core\Errors\LinePayTimeoutError;
use LinePay\Core\Errors\LinePayValidationError;

try {
    $response = $client->confirm($transactionId, 100, Currency::THB);
} catch (LinePayValidationError $e) {
    // ข้อผิดพลาดการตรวจสอบ (ก่อนเรียก API)
    echo "ข้อผิดพลาดการตรวจสอบ: " . $e->getMessage();
} catch (LinePayTimeoutError $e) {
    // หมดเวลา
    echo "หมดเวลาหลังจาก " . $e->getTimeout() . " วินาที";
} catch (LinePayError $e) {
    // ข้อผิดพลาด API
    echo "รหัสข้อผิดพลาด: " . $e->getReturnCode();
    echo "ข้อความ: " . $e->getReturnMessage();
}
```

## ปัญหาที่พบบ่อยและการแก้ไขปัญหา

### 🚫 การยืนยันซ้ำ (Error 1198)

แต่ละ `transactionId` สามารถยืนยันได้**เพียงครั้งเดียว**

* หากผู้ใช้รีเฟรชหน้าสำเร็จ เซิร์ฟเวอร์อาจพยายามยืนยันอีกครั้ง
* **วิธีแก้ไข:** ตรวจสอบสถานะคำสั่งซื้อในฐานข้อมูลของคุณ**ก่อน**เรียก `$client->confirm()` ถ้าเป็น "PAID" แล้ว ข้ามการเรียก API

```php
// ใน confirm callback handler
$order = Order::where('transaction_id', $transactionId)->first();

if ($order->status === 'PAID') {
    // ยืนยันแล้ว แสดงหน้าสำเร็จ
    return redirect()->route('payment.success');
}

// เรียก confirm เมื่อยังไม่ชำระเงินเท่านั้น
$response = $client->confirm($transactionId, $order->amount, Currency::THB);
$order->update(['status' => 'PAID']);
```

### 💰 จำนวนเงินไม่ตรงกัน (Error 1106)

จำนวนเงินที่ส่งไปยัง `confirm()` ต้องตรงกับจำนวนเงินที่ขอตรงทุกประการ

* **คำแนะนำ:** อย่าเชื่อจำนวนเงินใน URL query string (ถ้ามี) ให้ใช้ `orderId` ดึงจำนวนเงินจากฐานข้อมูลของคุณเสมอ

```php
// ✗ ไม่ดี: ใช้จำนวนเงินจาก query string
$amount = $request->input('amount'); // มีช่องโหว่!

// ✓ ดี: ใช้จำนวนเงินจากฐานข้อมูล
$order = Order::findOrFail($orderId);
$amount = $order->amount;
```

### ⏱️ ธุรกรรมหมดอายุ

`paymentUrl` และ `transactionId` มีเวลาหมดอายุ (ปกติ 20 นาที) หากผู้ใช้ใช้เวลานานเกินไป การเรียก confirm จะล้มเหลว

* เก็บเวลาหมดอายุและแสดงการนับถอยหลังให้ผู้ใช้
* จัดการข้อผิดพลาดหมดอายุอย่างเหมาะสมและอนุญาตให้ผู้ใช้เริ่มการชำระเงินใหม่

## การทดสอบ

```bash
composer install
composer test
composer analyze
```

## แพ็คเกจที่เกี่ยวข้อง

- [`carllee/line-pay-core-v4`](https://github.com/CarlLee1983/line-pay-core-v4-php) - Core SDK (dependency)
- [`carllee/line-pay-offline-v4`](https://github.com/CarlLee1983/line-pay-offline-v4-php) - Offline Payment SDK

## สัญญาอนุญาต

MIT License - ดูรายละเอียดที่ [LICENSE](LICENSE)

## แหล่งข้อมูล

- [เอกสาร LINE Pay API](https://pay.line.me/documents/online_v3_en.html)
- [LINE Pay Merchant Center](https://pay.line.me/portal/th/)
