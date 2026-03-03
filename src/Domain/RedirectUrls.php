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
     *
     * @throws \InvalidArgumentException If URLs are not valid
     */
    public function __construct(
        public readonly string $confirmUrl,
        public readonly string $cancelUrl
    ) {
        $this->validateUrl($confirmUrl, 'confirmUrl');
        $this->validateUrl($cancelUrl, 'cancelUrl');
    }

    /**
     * Validate URL format and scheme.
     *
     * @param string $url     The URL to validate
     * @param string $urlName The name of the URL parameter
     *
     * @throws \InvalidArgumentException If URL is invalid or not HTTPS
     */
    private function validateUrl(string $url, string $urlName): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid {$urlName} format: '{$url}' is not a valid URL");
        }

        // Verify HTTPS scheme (LINE Pay requires HTTPS, HTTP only allowed in sandbox with explicit opt-in)
        if (!str_starts_with($url, 'https://')) {
            throw new \InvalidArgumentException("Invalid {$urlName} format: '{$url}' must use HTTPS scheme");
        }
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
