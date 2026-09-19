<?php
namespace MoneyTracker\Tests;

use MoneyTracker\Transaction;
use MoneyTracker\Enums\TransactionType;
use PHPUnit\Framework\TestCase;


class TransactionTest extends TestCase
{
    public function testExpenseTransaction():void
    {
        $transaction = new Transaction(TransactionType::EXPENSE, 2500, "food");

        $this->assertTrue($transaction->isExpense());
        $this->assertFalse($transaction->isIncome());
        $this->assertSame("food", $transaction->getCategory());

    }

    public function testIncomeTransaction(): void
    {
        $transaction = new Transaction(TransactionType::INCOME, 50000, "salary");
        $this->assertTrue($transaction->isIncome());
        $this->assertFalse($transaction->isExpense());
        $this->assertSame("salary", $transaction->getCategory());
    }

    public function testTransactionTypeValue():void
    {
        $this->assertSame("expense", TransactionType::EXPENSE->value);
    }
}

