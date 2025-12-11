<?php

declare(strict_types=1);

namespace LinePay\Online\Enums;

/**
 * Confirm URL Type.
 *
 * - CLIENT: User is redirected to the confirmUrl (Client-side)
 * - SERVER: Server-to-server confirmation (less common for standard web flow)
 */
enum ConfirmUrlType: string
{
    case CLIENT = 'CLIENT';
    case SERVER = 'SERVER';
}
