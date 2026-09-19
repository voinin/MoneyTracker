<?php
namespace MoneyTracker\Tests;
use MoneyTracker\SavingsAccount;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class SavingAccountTest extends TestCase
{
    public function testInterestAccrual()
    {
        $account = new SavingsAccount(
            "Накопительный",
            10000,
            "RUB");
        $account->addInterest(10);
        $this->assertSame(11000.0, $account->getBalance());
    }

    public function testAddInterestWithInvalidAmount():void
    {
        $account = new SavingsAccount(
            "Накопительный",
            10000,
            "RUB");
        $this->expectException(InvalidArgumentException::class);
        $account->addInterest(0);
    }
}