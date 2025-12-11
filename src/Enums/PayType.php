<?php

declare(strict_types=1);

namespace LinePay\Online\Enums;

/**
 * Payment Type.
 *
 * - NORMAL: One-time payment
 * - PREAPPROVED: Preapproved payment (for automatic/recurring payments)
 */
enum PayType: string
{
    case NORMAL = 'NORMAL';
    case PREAPPROVED = 'PREAPPROVED';
}
