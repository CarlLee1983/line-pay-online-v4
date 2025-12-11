<?php

declare(strict_types=1);

namespace LinePay\Online\Tests;

use LinePay\Online\Enums\ConfirmUrlType;
use LinePay\Online\Enums\Currency;
use LinePay\Online\Enums\PayType;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function testCurrencyValues(): void
    {
        $this->assertEquals('TWD', Currency::TWD->value);
        $this->assertEquals('JPY', Currency::JPY->value);
        $this->assertEquals('THB', Currency::THB->value);
        $this->assertEquals('USD', Currency::USD->value);
    }

    public function testPayTypeValues(): void
    {
        $this->assertEquals('NORMAL', PayType::NORMAL->value);
        $this->assertEquals('PREAPPROVED', PayType::PREAPPROVED->value);
    }

    public function testConfirmUrlTypeValues(): void
    {
        $this->assertEquals('CLIENT', ConfirmUrlType::CLIENT->value);
        $this->assertEquals('SERVER', ConfirmUrlType::SERVER->value);
    }
}
