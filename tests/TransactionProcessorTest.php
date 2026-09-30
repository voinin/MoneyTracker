<?php

namespace MoneyTracker\Tests;

use InvalidArgumentException;
use MoneyTracker\Account;
use MoneyTracker\Database;
use MoneyTracker\Transaction;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Repositories\DatabaseAccountRepository;
use MoneyTracker\Services\TransactionProcessor;
use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Repositories\AccountRepositoryInterface;

class TransactionProcessorTest extends TestCase
{
    public function testTransactionProcessReturnTrue():void
    {
        $account = new Account("Основная карта", 2500, "RUB", 99);
        $database = $this->createStub(Database::class);
        $repisitoryAccounts = $this->createMock(AccountRepositoryInterface::class);
        $repositoryTransactions = $this->createMock(TransactionRepositoryInterface::class);
        $transaction = new Transaction(TransactionType::EXPENSE, 1000, "food");
        $newBalance = $account->getBalance() - $transaction->getAmount();
        $repositoryTransactions
            ->expects($this->once())
            ->method('save')
            ->with($transaction);
        $repisitoryAccounts
            ->expects($this->once())
            ->method('updateAccountBalance')
            ->with($account->getId(), $newBalance);
        $process = new TransactionProcessor($repositoryTransactions, $repisitoryAccounts, $database);
        $this->assertTrue($process->processTransaction($transaction, $account));
        $this->assertSame(1500.0, $account->getBalance());
    }

    public function testInvalidTransactionDoesntSave():void
    {
        $account = new Account("Основная карта", 2500, "RUB");
        $database = $this->createStub(Database::class);
        $repisitoryAccounts = $this->createMock(AccountRepositoryInterface::class);
        $repositoryTransactions = $this->createMock(TransactionRepositoryInterface::class);
        $transaction = new Transaction(TransactionType::EXPENSE, 3000, "food");
        $repositoryTransactions
            ->expects($this->never())
            ->method('save');
        $repisitoryAccounts
            ->expects($this->never())
            ->method('updateAccountBalance');
        $this->expectException(InvalidArgumentException::class);
        $processor = new TransactionProcessor($repositoryTransactions, $repisitoryAccounts, $database);
        $processor->processTransaction($transaction, $account);
    }
}

