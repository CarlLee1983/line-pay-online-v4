<?php

declare(strict_types=1);

namespace LinePay\Online\Payments;

use LinePay\Core\Errors\LinePayValidationError;
use LinePay\Online\Domain\PaymentOptions;
use LinePay\Online\Domain\PaymentPackage;
use LinePay\Online\Domain\RedirectUrls;
use LinePay\Online\Enums\Currency;
use LinePay\Online\LinePayClient;

/**
 * Payment Request Operation.
 *
 * A fluent builder pattern implementation for constructing, validating, and executing
 * the Request Payment API (POST /v4/payments/request).
 *
 * @example
 * ```php
 * $response = $client->payment()
 *     ->setAmount(100)
 *     ->setCurrency(Currency::TWD)
 *     ->setOrderId('ORDER_001')
 *     ->addPackage($package)
 *     ->setRedirectUrls('https://...', 'https://...')
 *     ->send();
 * ```
 */
class RequestPayment
{
    private ?int $amount = null;
    private ?Currency $currency = null;
    private ?string $orderId = null;

    /** @var PaymentPackage[] */
    private array $packages = [];

    private ?RedirectUrls $redirectUrls = null;
    private ?PaymentOptions $options = null;

    /**
     * Creates a new RequestPayment instance.
     *
     * @param LinePayClient $client The LinePayClient instance to use for sending the request
     */
    public function __construct(
        private readonly LinePayClient $client
    ) {
    }

    /**
     * Sets the total payment amount.
     *
     * @param int $amount The total transaction amount (must be non-negative)
     *
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Sets the payment currency.
     *
     * @param Currency $currency The currency code
     *
     * @return $this
     */
    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Sets the merchant's unique order ID.
     *
     * @param string $orderId A unique identifier for the order
     *
     * @return $this
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Adds a payment package to the request.
     *
     * @param PaymentPackage $package The payment package containing products
     *
     * @return $this
     */
    public function addPackage(PaymentPackage $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    /**
     * Sets the redirect URLs for payment flow.
     *
     * @param string $confirmUrl The URL to redirect the user to after successful payment
     * @param string $cancelUrl  The URL to redirect the user to if they cancel
     *
     * @return $this
     */
    public function setRedirectUrls(string $confirmUrl, string $cancelUrl): self
    {
        $this->redirectUrls = new RedirectUrls($confirmUrl, $cancelUrl);

        return $this;
    }

    /**
     * Sets optional payment configurations.
     *
     * @param PaymentOptions $options Additional options
     *
     * @return $this
     */
    public function setOptions(PaymentOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Validates the constructed request parameters.
     *
     * @throws LinePayValidationError If any required field is missing or invalid
     */
    public function validate(): void
    {
        if ($this->amount === null || $this->amount < 0) {
            throw new LinePayValidationError('Amount is required and must be non-negative', 'amount');
        }
        if ($this->currency === null) {
            throw new LinePayValidationError('Currency is required', 'currency');
        }
        if ($this->orderId === null || $this->orderId === '') {
            throw new LinePayValidationError('OrderId is required', 'orderId');
        }
        if (count($this->packages) === 0) {
            throw new LinePayValidationError('At least one package is required', 'packages');
        }
        if ($this->redirectUrls === null) {
            throw new LinePayValidationError('Redirect URLs are required', 'redirectUrls');
        }

        // Validate Package Amounts Sum
        $packagesTotal = array_reduce(
            $this->packages,
            fn (int $sum, PaymentPackage $pkg) => $sum + $pkg->amount,
            0
        );

        if ($packagesTotal !== $this->amount) {
            throw new LinePayValidationError(
                "Sum of package amounts ({$packagesTotal}) does not match total amount ({$this->amount})",
                'packages'
            );
        }

        // Validate Products Sum within each Package
        foreach ($this->packages as $index => $pkg) {
            $productsTotal = array_reduce(
                $pkg->getProducts(),
                fn (int $sum, $prod) => $sum + ($prod->quantity * $prod->price),
                0
            );

            if ($productsTotal !== $pkg->amount) {
                throw new LinePayValidationError(
                    "Sum of product amounts ({$productsTotal}) in package index {$index} does not match package amount ({$pkg->amount})",
                    "packages[{$index}].products"
                );
            }
        }
    }

    /**
     * Builds the final request body.
     *
     * @return array<string, mixed> The constructed request body ready for the API
     *
     * @throws LinePayValidationError If validation fails
     */
    public function toBody(): array
    {
        $this->validate();

        if (
            $this->amount === null ||
            $this->currency === null ||
            $this->orderId === null ||
            $this->redirectUrls === null
        ) {
            throw new LinePayValidationError('Validation failed unexpectedly');
        }

        $body = [
            'amount' => $this->amount,
            'currency' => $this->currency->value,
            'orderId' => $this->orderId,
            'packages' => array_map(fn (PaymentPackage $pkg) => $pkg->toArray(), $this->packages),
            'redirectUrls' => $this->redirectUrls->toArray(),
        ];

        if ($this->options !== null) {
            $optionsArray = $this->options->toArray();
            if (count($optionsArray) > 0) {
                $body['options'] = $optionsArray;
            }
        }

        return $body;
    }

    /**
     * Executes the payment request.
     *
     * @return array<string, mixed> The API response
     *
     * @throws LinePayValidationError If validation fails
     * @throws \LinePay\Core\Errors\LinePayError If the API request fails
     */
    public function send(): array
    {
        $body = $this->toBody();

        return $this->client->requestPayment($body);
    }
}
