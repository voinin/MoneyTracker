<?php

namespace MoneyTracker\Tests;

use InvalidArgumentException;
use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Transaction;
use PDOStatement;
use PDO;
use RuntimeException;
use Symfony\Component\VarDumper\Cloner\Data;

class DatabaseTransactionRepositoryTest extends TestCase
{
    private function createRepositoryWithMock(\PDOStatement $stmt):DatabaseTransactionRepository
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $database = $this->createMock(Database::class);
        $database
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdo);
        
        return new DatabaseTransactionRepository($database);
    }
    public function testGetTransactionByIdOrFailWhenFounds()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':id' => 1])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn([
                'id'       => 1,
                'type'     => 'expense',
                'amount'   => 2500,
                'category' => 'food',]);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $transaction = $repositoryTransactions->getTransactionByIdOrFail(1);
        $this->assertSame(TransactionType::EXPENSE, $transaction->getType());
        $this->assertSame(2500.0, $transaction->getAmount());
        $this->assertSame("food", $transaction->getCategory());
    }

    public function testGetTransactionByIdOrFailWhenFail()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':id' => 1])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $this->expectException(RuntimeException::class);
        $repositoryTransactions->getTransactionByIdOrFail(1);   
    }
    
    public function testGetTransactionCountByTypeIncome()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':type' => TransactionType::INCOME->value])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(3);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $this->assertSame(3, $repositoryTransactions->getTransactionsCountByType(TransactionType::INCOME));
    }
    public function testGetTransactionCountByTypeExpense()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':type' => TransactionType::EXPENSE->value])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(5);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $this->assertSame(5, $repositoryTransactions->getTransactionsCountByType(TransactionType::EXPENSE));
    }

    public function testGetTransactionsByCategory()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':category' => 'food'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with()
            ->willReturn([[
                'type'     => 'expense',
                'amount'   => 2500,
                'category' => 'food',
                'id' => 98],
                [
                'type'     => 'expense',
                'amount'   => 500,
                'category' => 'food',
                'id' => 99]]);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $transactions = $repositoryTransactions->getTransactionsByCategory("food");
        $this->assertSame("food", $transactions[0]->getCategory());
        $this->assertSame("food", $transactions[1]->getCategory());
        $this->assertCount(2, $transactions);
        $this->assertSame(2500.0, $transactions[0]->getAmount());
        $this->assertSame(500.0, $transactions[1]->getAmount());
    }

    public function testGetTransactionByCategoryReturnNothing()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->with([':category' => 'food'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with()
            ->willReturn([]);

        $repositoryTransactions = $this->createRepositoryWithMock($stmt);
        $this->assertSame([], $repositoryTransactions->getTransactionsByCategory("food"));
    }

    public function testGetLatestTransaction()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([[
                'type'     => 'expense',
                'amount'   => 2500,
                'category' => 'food',
                'id' => 98],
                [
                'type'     => 'expense',
                'amount'   => 500,
                'category' => 'food',
                'id' => 99]]);
    
        $pdo = $this->createMock(\PDO::class);
        $pdo
            ->expects($this->once())
            ->method('query')
            ->willReturn($stmt);

        $database = $this->createMock(Database::class);
        $database
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdo);

        $repositoryTransactions = new DatabaseTransactionRepository($database);
        $transactions = $repositoryTransactions->getLatestTransactions(2);
        $this->assertCount(2, $transactions);
    }

    public function testGetLatestTransactionsThrowsOnInvalidLimit(): void
    {
        $database = $this->createMock(Database::class);
        $database
        ->expects($this->never())
        ->method('getConnection');

        $repositoryTransactions = new DatabaseTransactionRepository($database);

        $this->expectException(InvalidArgumentException::class);

        $repositoryTransactions->getLatestTransactions(0);
    }
}

