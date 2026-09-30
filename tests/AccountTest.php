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

    public function testGetId()
    {
        $account = new Account("test", 500, "RUB", 99);
        $this->assertSame(99, $account->getId());
    }

    public function testGetIdReturnNull()
    {
        $account = new Account("test", 500, "RUB");
        $this->assertNull($account->getId());
    }

    public function testSetBalance()
    {
        $account = new Account("test", 500, "RUB");
        $account->setBalance(9999.0);
        $this->assertSame(9999.0, $account->getBalance());
    }

    public function testSetBalanceThrowException()
    {
        $account = new Account("test", 500, "RUB");
        $this->expectException(InvalidArgumentException::class);
        $account->setBalance(-9999.0);
    }
}