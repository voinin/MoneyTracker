<?php

namespace MoneyTracker\Tests;
use MoneyTracker\Account;
use MoneyTracker\Transaction;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Services\TransactionProcessor;
use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    public function testRigthTransactionProcess():void
    {
        $account = new Account("Основная карта", 2500, "RUB");
        $repository = new TransactionRepository;
        $transaction = new Transaction(TransactionType::EXPENSE, 1000, "food");
        $process = new TransactionProcessor($repository);
        $process->processTransaction($transaction, $account);
        $this->assertSame(1500.0, $account->getBalance());
    }

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
    }
}

