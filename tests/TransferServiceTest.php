<?php

namespace MoneyTracker\Tests;

use InvalidArgumentException;
use MoneyTracker\Services\TransferService;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Account;
use MoneyTracker\AccountInterface;

class TransferServiceTest extends TestCase
{
    public function testTransferIsSuccess(): void
{
    $from = $this->createMock(AccountInterface::class);
    $to   = $this->createMock(AccountInterface::class);

    $from->method('getCurrency')->willReturn("RUB");
    $from
        ->expects($this->once())
        ->method('withdraw')
        ->with(1000.0)
        ->willReturn(true);

    $to->method('getCurrency')->willReturn("RUB");
    $to
        ->expects($this->once())
        ->method('deposit')
        ->with(1000.0)
        ->willReturn(true);

    $transfer = new TransferService();
    $this->assertTrue($transfer->transfer($from, $to, 1000));
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

    public function testValidateCurrencyError():void
    {
        $from = new Account("Основная карта", 2000, "RUB");
        $to = new Account("Наличные", 5000, "USD");
        $transfer = new TransferService;
        $this->expectException(InvalidArgumentException::class);
        $transfer->transfer($from, $to, 1000);
    }

    public function testVaildateZeroAmount():void
    {
        $from = new Account("Основная карта", 2000, "RUB");
        $to = new Account("Наличные", 5000, "USD");
        $transfer = new TransferService();
        $this->expectException(InvalidArgumentException::class);
        $transfer->transfer($from, $to, 0); 
    }

    public function testTransferWithSucessWithdrawAndInvalidDeposit():void
    {
        $from = $this->createMock(AccountInterface::class);
        $to = $this->createMock(AccountInterface::class);

        $from 
            ->expects($this->once())
            ->method('withdraw')
            ->willReturn(true);

        $to
            ->expects($this->once())
            ->method('deposit')
            ->willReturn(false);
        
        $transfer = new TransferService();
        $this->expectException(InvalidArgumentException::class);
        $transfer->transfer($from, $to, 1);
    }
}