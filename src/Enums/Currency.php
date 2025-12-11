<?php

declare(strict_types=1);

namespace LinePay\Online\Enums;

/**
 * ISO 4217 Currency Codes.
 *
 * Represents the 3-letter currency codes defined in ISO 4217.
 * LINE Pay primarily supports TWD, JPY, and THB.
 */
enum Currency: string
{
    // LINE Pay Primary Supported Currencies
    case TWD = 'TWD'; // New Taiwan Dollar
    case JPY = 'JPY'; // Japanese Yen
    case THB = 'THB'; // Thai Baht

    // Other Common Currencies
    case USD = 'USD'; // United States Dollar
    case EUR = 'EUR'; // Euro
    case GBP = 'GBP'; // British Pound Sterling
    case AUD = 'AUD'; // Australian Dollar
    case CAD = 'CAD'; // Canadian Dollar
    case CHF = 'CHF'; // Swiss Franc
    case CNY = 'CNY'; // Chinese Yuan
    case HKD = 'HKD'; // Hong Kong Dollar
    case KRW = 'KRW'; // South Korean Won
    case SGD = 'SGD'; // Singapore Dollar
    case MYR = 'MYR'; // Malaysian Ringgit
    case PHP = 'PHP'; // Philippine Peso
    case IDR = 'IDR'; // Indonesian Rupiah
    case VND = 'VND'; // Vietnamese Dong
    case INR = 'INR'; // Indian Rupee
    case NZD = 'NZD'; // New Zealand Dollar
}
