<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

/**
 * Redirect URLs configuration.
 */
class RedirectUrls
{
    /**
     * Create a new RedirectUrls instance.
     *
     * @param string $confirmUrl URL to redirect after successful payment
     * @param string $cancelUrl  URL to redirect after cancellation
     */
    public function __construct(
        public readonly string $confirmUrl,
        public readonly string $cancelUrl
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'confirmUrl' => $this->confirmUrl,
            'cancelUrl' => $this->cancelUrl,
        ];
    }
}
