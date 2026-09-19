<?php

namespace MoneyTracker\Tests;

use InvalidArgumentException;
use MoneyTracker\Services\TransferService;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Account;

class TransferServiceTest extends TestCase
{
    public function testTransferIsSuccess():void
    {
        $from = new Account("Основная карта", 2000, "RUB");
        $to = new Account("Наличные", 5000, "RUB");
        $transfer = new TransferService;
        $this->assertTrue($transfer->transfer($from, $to, 1000));
        $this->assertSame(1000.0, $from->getBalance());
        $this->assertSame(6000.0, $to->getBalance());
    }

    public function testTransferWithInsufficientBalance():void
    {
        $from = new Account("Основная карта", 2000, "RUB");
        $to = new Account("Наличные", 5000, "RUB");
        $transfer = new TransferService;
        $this->expectException(InvalidArgumentException::class);
        $transfer->transfer($from, $to, 30000);
    }

    public function testTransferWithInvalidAmount():void
    {
        $from = new Account("Основная карта", 2000, "RUB");
        $to = new Account("Наличные", 5000, "RUB");
        $transfer = new TransferService;
        $this->expectException(InvalidArgumentException::class);
        $transfer->transfer($from, $to, -1000);
    }
}