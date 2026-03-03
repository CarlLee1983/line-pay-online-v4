<?php

declare(strict_types=1);

namespace LinePay\Online\Enums;

/**
 * ISO 4217 Currency Codes.
 *
 * Represents the 3-letter currency codes defined in ISO 4217.
 *
 * **OFFICIALLY SUPPORTED BY LINE PAY (as of 2024):**
 * - TWD (Taiwan)
 * - JPY (Japan)
 * - THB (Thailand)
 *
 * **NOTE:** Other currencies below are included for future support or experimental purposes.
 * They are NOT currently supported by the LINE Pay API. Using them will result in API errors.
 * Check the official LINE Pay documentation for the latest list of supported currencies.
 *
 * @see https://pay.line.me/developers
 */
enum Currency: string
{
    // ============================================================================
    // OFFICIALLY SUPPORTED CURRENCIES
    // ============================================================================
    case TWD = 'TWD'; // New Taiwan Dollar - SUPPORTED
    case JPY = 'JPY'; // Japanese Yen - SUPPORTED
    case THB = 'THB'; // Thai Baht - SUPPORTED

    // ============================================================================
    // NOT CURRENTLY SUPPORTED - For Future Use
    // ============================================================================
    case USD = 'USD'; // United States Dollar - NOT SUPPORTED
    case EUR = 'EUR'; // Euro - NOT SUPPORTED
    case GBP = 'GBP'; // British Pound Sterling - NOT SUPPORTED
    case AUD = 'AUD'; // Australian Dollar - NOT SUPPORTED
    case CAD = 'CAD'; // Canadian Dollar - NOT SUPPORTED
    case CHF = 'CHF'; // Swiss Franc - NOT SUPPORTED
    case CNY = 'CNY'; // Chinese Yuan - NOT SUPPORTED
    case HKD = 'HKD'; // Hong Kong Dollar - NOT SUPPORTED
    case KRW = 'KRW'; // South Korean Won - NOT SUPPORTED
    case SGD = 'SGD'; // Singapore Dollar - NOT SUPPORTED
    case MYR = 'MYR'; // Malaysian Ringgit - NOT SUPPORTED
    case PHP = 'PHP'; // Philippine Peso - NOT SUPPORTED
    case IDR = 'IDR'; // Indonesian Rupiah - NOT SUPPORTED
    case VND = 'VND'; // Vietnamese Dong - NOT SUPPORTED
    case INR = 'INR'; // Indian Rupee - NOT SUPPORTED
    case NZD = 'NZD'; // New Zealand Dollar - NOT SUPPORTED

    /**
     * Check if this currency is officially supported by LINE Pay.
     *
     * @return bool True if the currency is supported, false otherwise
     */
    public function isSupported(): bool
    {
        return match ($this) {
            self::TWD, self::JPY, self::THB => true,
            default => false,
        };
    }
}
