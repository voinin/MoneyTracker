<?php

namespace MoneyTracker\Tests;

use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Transaction;
use PDOStatement;
use PDO;
use RuntimeException;

class DatabaseTransactionRepositoryTest extends TestCase
{
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
        $repository = new DatabaseTransactionRepository($database);
        $transaction = $repository->getTransactionByIdOrFail(1);
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
        $repository = new DatabaseTransactionRepository($database);
        $this->expectException(RuntimeException::class);
        $repository->getTransactionByIdOrFail(1);   
    }
}

