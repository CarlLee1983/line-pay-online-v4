<?php

declare(strict_types=1);

namespace LinePay\Online;

use LinePay\Core\LinePayBaseClient;
use LinePay\Core\LinePayUtils;
use LinePay\Online\Enums\Currency;
use LinePay\Online\Payments\RequestPayment;

/**
 * LINE Pay Client.
 *
 * Core class for interacting with LINE Pay Online V4 API.
 *
 * @example
 * ```php
 * use LinePay\Core\Config\LinePayConfig;
 * use LinePay\Online\LinePayClient;
 * use LinePay\Online\Enums\Currency;
 *
 * $config = new LinePayConfig(
 *     channelId: getenv('LINE_PAY_CHANNEL_ID'),
 *     channelSecret: getenv('LINE_PAY_CHANNEL_SECRET'),
 *     env: 'sandbox'
 * );
 *
 * $client = new LinePayClient($config);
 *
 * // Using Builder Pattern
 * $response = $client->payment()
 *     ->setAmount(100)
 *     ->setCurrency(Currency::TWD)
 *     ->setOrderId('ORDER_001')
 *     ->addPackage($package)
 *     ->setRedirectUrls('https://...', 'https://...')
 *     ->send();
 * ```
 */
class LinePayClient extends LinePayBaseClient
{
    /**
     * Create a new RequestPayment builder.
     *
     * Factory method for fluent payment request construction.
     */
    public function payment(): RequestPayment
    {
        return new RequestPayment($this);
    }

    /**
     * Request Payment (POST /v4/payments/request).
     *
     * @param array<string, mixed> $body Payment Request Body
     *
     * @return array<string, mixed> API Response
     */
    public function requestPayment(array $body): array
    {
        return $this->sendRequest('POST', '/v4/payments/request', $body);
    }

    /**
     * Confirm Payment (POST /v4/payments/{transactionId}/confirm).
     *
     * @param string         $transactionId 交易 ID（19 位數字）
     * @param int            $amount        確認金額
     * @param Currency|string $currency      貨幣代碼
     *
     * @return array<string, mixed> API Response
     */
    public function confirm(string $transactionId, int $amount, Currency|string $currency): array
    {
        LinePayUtils::validateTransactionId($transactionId);

        $currencyValue = $currency instanceof Currency ? $currency->value : $currency;

        return $this->sendRequest(
            'POST',
            '/v4/payments/' . urlencode($transactionId) . '/confirm',
            [
                'amount' => $amount,
                'currency' => $currencyValue,
            ]
        );
    }

    /**
     * Capture Payment (POST /v4/payments/authorizations/{transactionId}/capture).
     *
     * @param string         $transactionId 交易 ID（19 位數字）
     * @param int            $amount        請款金額
     * @param Currency|string $currency      貨幣代碼
     *
     * @return array<string, mixed> API Response
     */
    public function capture(string $transactionId, int $amount, Currency|string $currency): array
    {
        LinePayUtils::validateTransactionId($transactionId);

        $currencyValue = $currency instanceof Currency ? $currency->value : $currency;

        return $this->sendRequest(
            'POST',
            '/v4/payments/authorizations/' . urlencode($transactionId) . '/capture',
            [
                'amount' => $amount,
                'currency' => $currencyValue,
            ]
        );
    }

    /**
     * Void Payment (POST /v4/payments/authorizations/{transactionId}/void).
     *
     * @param string $transactionId 交易 ID（19 位數字）
     *
     * @return array<string, mixed> API Response
     */
    public function void(string $transactionId): array
    {
        LinePayUtils::validateTransactionId($transactionId);

        return $this->sendRequest(
            'POST',
            '/v4/payments/authorizations/' . urlencode($transactionId) . '/void',
            []
        );
    }

    /**
     * Refund Payment (POST /v4/payments/{transactionId}/refund).
     *
     * @param string   $transactionId 交易 ID（19 位數字）
     * @param int|null $refundAmount  退款金額（可選，不指定則全額退款）
     *
     * @return array<string, mixed> API Response
     */
    public function refund(string $transactionId, ?int $refundAmount = null): array
    {
        LinePayUtils::validateTransactionId($transactionId);

        $body = [];
        if ($refundAmount !== null) {
            $body['refundAmount'] = $refundAmount;
        }

        return $this->sendRequest(
            'POST',
            '/v4/payments/' . urlencode($transactionId) . '/refund',
            $body
        );
    }

    /**
     * Get Payment Details (GET /v4/payments/requests).
     *
     * @param string[]|null $transactionIds Transaction IDs to search
     * @param string[]|null $orderIds       Order IDs to search
     * @param string|null   $fields         Fields to retrieve
     *
     * @return array<string, mixed> API Response
     */
    public function getDetails(
        ?array $transactionIds = null,
        ?array $orderIds = null,
        ?string $fields = null
    ): array {
        $params = [];

        if ($transactionIds !== null && count($transactionIds) > 0) {
            $params['transactionId'] = implode(',', $transactionIds);
        }

        if ($orderIds !== null && count($orderIds) > 0) {
            $params['orderId'] = implode(',', $orderIds);
        }

        if ($fields !== null && $fields !== '') {
            $params['fields'] = $fields;
        }

        return $this->sendRequest('GET', '/v4/payments/requests', null, $params);
    }

    /**
     * Check Payment Status (GET /v4/payments/requests/{transactionId}/check).
     *
     * @param string $transactionId 交易 ID（19 位數字）
     *
     * @return array<string, mixed> API Response
     */
    public function checkStatus(string $transactionId): array
    {
        LinePayUtils::validateTransactionId($transactionId);

        return $this->sendRequest(
            'GET',
            '/v4/payments/requests/' . urlencode($transactionId) . '/check'
        );
    }
}
