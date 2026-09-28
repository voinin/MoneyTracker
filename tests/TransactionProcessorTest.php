<?php

namespace MoneyTracker\Tests;

use InvalidArgumentException;
use MoneyTracker\Account;
use MoneyTracker\Transaction;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Services\TransactionProcessor;
use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    public function testTransactionProcessReturnTrue():void
    {
        $account = new Account("Основная карта", 2500, "RUB");
        $repository = $this->createMock(TransactionRepositoryInterface::class);
        $transaction = new Transaction(TransactionType::EXPENSE, 1000, "food");
        $repository
            ->expects($this->once())
            ->method('save')
            ->with($transaction);
        $process = new TransactionProcessor($repository);
        $this->assertTrue($process->processTransaction($transaction, $account));
        $this->assertSame(1500.0, $account->getBalance());
    }

    public function testInvalidTransactionDoesntSave():void
    {
        $account = new Account("Основная карта", 2500, "RUB");
        $transaction = new Transaction(TransactionType::EXPENSE, 3000, "food");
        $repository = $this->createMock(TransactionRepositoryInterface::class);
        $repository
            ->expects($this->never())
            ->method('save');
        $this->expectException(InvalidArgumentException::class);
        $processor = new TransactionProcessor($repository);
        $processor->processTransaction($transaction, $account);
    }
}

