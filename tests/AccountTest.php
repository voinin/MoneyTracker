<?php

namespace MoneyTracker\Tests;

use MoneyTracker\Account;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;


class AccountTest extends TestCase
{
    public function testDepositWithInvalidAmount():void
    {
        $account = new Account("Основная карта", 25000, "RUB");
        $this->expectException(InvalidArgumentException::class);
        $account->deposit(-2000);
    }

    public function testDepositIncresesBalance():void
    {
        $account = new Account("test", 25000, "RUB");
        $account->deposit(5000);
        $this->assertSame(30000.0, $account->getBalance());
    }

    public function testWithdrawDecresesBalance():void
    {
        $account = new Account("test", 25000, "RUB");
        $account->withdraw(5000);
        $this->assertSame(20000.0, $account->getBalance());
    }

    public function testWithdrawWithInsufficientBalance(): void
    {
        $account = new Account("test", 25000, "RUB");
        $this->expectException(InvalidArgumentException::class);
        $account->withdraw(30000);
    }

    public function testDepositExceptionMessage():void
    {
        $account = new Account("test", 2500, "RUB");
        $this->expectExceptionMessage("Введена некорректная сумма!");
        $account->deposit(-3000);
    }
}