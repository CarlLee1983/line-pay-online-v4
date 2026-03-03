<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

use LinePay\Online\Enums\ConfirmUrlType;
use LinePay\Online\Enums\PayType;

/**
 * Payment Options.
 *
 * Optional configurations for the payment request.
 */
class PaymentOptions
{
    /**
     * @param bool|null              $capture                Whether to capture immediately
     * @param PayType|null           $payType                Payment type (NORMAL or PREAPPROVED)
     * @param string|null            $locale                 Display locale (e.g., 'en', 'zh-Hant')
     * @param bool|null              $checkConfirmUrlBrowser Whether to check confirmUrl browser
     * @param string|null            $branchName             Branch name
     * @param string|null            $branchId               Branch ID
     * @param ConfirmUrlType|null    $confirmUrlType         Confirmation URL type (CLIENT or SERVER)
     */
    public function __construct(
        public readonly ?bool $capture = null,
        public readonly ?PayType $payType = null,
        public readonly ?string $locale = null,
        public readonly ?bool $checkConfirmUrlBrowser = null,
        public readonly ?string $branchName = null,
        public readonly ?string $branchId = null,
        public readonly ?ConfirmUrlType $confirmUrlType = null
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payment = array_filter([
            'capture' => $this->capture,
            'payType' => $this->payType?->value,
            'confirmUrlType' => $this->confirmUrlType?->value,
        ], fn ($v) => $v !== null);

        $display = array_filter([
            'locale' => $this->locale,
            'checkConfirmUrlBrowser' => $this->checkConfirmUrlBrowser,
        ], fn ($v) => $v !== null);

        $extra = array_filter([
            'branchName' => $this->branchName,
            'branchId' => $this->branchId,
        ], fn ($v) => $v !== null);

        return array_filter([
            'payment' => $payment,
            'display' => $display,
            'extra' => $extra,
        ], fn ($v) => !empty($v));
    }
}
