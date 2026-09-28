<?php
namespace MoneyTracker\Tests;

use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Transaction;
use MoneyTracker\TransactionStatistics;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class TransactionStatisticsTest extends TestCase
{
    public function testCalculateIncome():void
    {
        $statistics = new TransactionStatistics();
        $transactions = [
            new Transaction(TransactionType::INCOME, 2000, "salart"),
            new Transaction(TransactionType::EXPENSE, 2000, "food"),
            new Transaction(TransactionType::INCOME, 3000, "salary")
        ];
        $this->assertSame(5000.0, $statistics->calculateIncome($transactions));
    }

    public function testCalculateIncomeReturnsZero():void
    {
        $statistics = new TransactionStatistics();
        $this->assertSame(0.0, $statistics->calculateIncome([]));
    }

    public function testCalculateExpense():void
    {
        $statistics = new TransactionStatistics();
        $transactions = [
            new Transaction(TransactionType::INCOME, 2000, "salary"),
            new Transaction(TransactionType::EXPENSE, 2000, "food"),
            new Transaction(TransactionType::EXPENSE, 3000, "food")
        ];
        $this->assertSame(5000.0, $statistics->calculateExpense($transactions));
    }

    public function testCalculateExpenseReturnsZero():void
    {
        $statistics = new TransactionStatistics();
        $this->assertSame(0.0, $statistics->calculateExpense([]));
    }

    public function testCalculateBalance():void
    {
        $statistics = new TransactionStatistics();
        $transactions = [
            new Transaction(TransactionType::INCOME, 2000, "salart"),
            new Transaction(TransactionType::EXPENSE, 2000, "food"),
            new Transaction(TransactionType::INCOME, 3000, "salary")
        ];
        $this->assertSame(3000.0, $statistics->calculateBalance($transactions));
    }

    public function testCalculateBalanceReturnsZero():void
    {
        $statistics = new TransactionStatistics();
        $this->assertSame(0.0, $statistics->calculateBalance([]));
    }
}