<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

use LinePay\Online\Enums\PayType;

/**
 * Payment Options.
 *
 * Optional configurations for the payment request.
 */
class PaymentOptions
{
    /**
     * @param bool|null    $capture                Whether to capture immediately
     * @param PayType|null $payType                Payment type (NORMAL or PREAPPROVED)
     * @param string|null  $locale                 Display locale (e.g., 'en', 'zh-Hant')
     * @param bool|null    $checkConfirmUrlBrowser Whether to check confirmUrl browser
     * @param string|null  $branchName             Branch name
     * @param string|null  $branchId               Branch ID
     */
    public function __construct(
        public readonly ?bool $capture = null,
        public readonly ?PayType $payType = null,
        public readonly ?string $locale = null,
        public readonly ?bool $checkConfirmUrlBrowser = null,
        public readonly ?string $branchName = null,
        public readonly ?string $branchId = null
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $options = [];

        // Payment options
        $payment = [];
        if ($this->capture !== null) {
            $payment['capture'] = $this->capture;
        }
        if ($this->payType !== null) {
            $payment['payType'] = $this->payType->value;
        }
        if (count($payment) > 0) {
            $options['payment'] = $payment;
        }

        // Display options
        $display = [];
        if ($this->locale !== null) {
            $display['locale'] = $this->locale;
        }
        if ($this->checkConfirmUrlBrowser !== null) {
            $display['checkConfirmUrlBrowser'] = $this->checkConfirmUrlBrowser;
        }
        if (count($display) > 0) {
            $options['display'] = $display;
        }

        // Extra options
        $extra = [];
        if ($this->branchName !== null) {
            $extra['branchName'] = $this->branchName;
        }
        if ($this->branchId !== null) {
            $extra['branchId'] = $this->branchId;
        }
        if (count($extra) > 0) {
            $options['extra'] = $extra;
        }

        return $options;
    }
}
