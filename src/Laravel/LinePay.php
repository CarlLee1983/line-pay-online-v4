<?php

declare(strict_types=1);

namespace LinePay\Online\Laravel;

use Illuminate\Support\Facades\Facade;
use LinePay\Online\LinePayClient;

/**
 * LINE Pay Facade for Laravel.
 *
 * Provides static access to LinePayClient methods via Laravel's Facade system.
 *
 * @method static \LinePay\Online\Payments\RequestPayment payment()
 * @method static array<string, mixed> requestPayment(array<string, mixed> $body)
 * @method static array<string, mixed> confirm(string $transactionId, int $amount, \LinePay\Online\Enums\Currency|string $currency)
 * @method static array<string, mixed> capture(string $transactionId, int $amount, \LinePay\Online\Enums\Currency|string $currency)
 * @method static array<string, mixed> void(string $transactionId)
 * @method static array<string, mixed> refund(string $transactionId, ?int $refundAmount = null)
 * @method static array<string, mixed> getDetails(?array $transactionIds = null, ?array $orderIds = null, ?string $fields = null)
 * @method static array<string, mixed> checkStatus(string $transactionId)
 *
 * @see \LinePay\Online\LinePayClient
 */
class LinePay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return LinePayClient::class;
    }
}
