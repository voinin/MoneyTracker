<?php
namespace MoneyTracker\Tests\Integration;

use InvalidArgumentException;
use MoneyTracker\Account;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Repositories\DatabaseAccountRepository;
use MoneyTracker\Services\TransactionProcessor;
use MoneyTracker\Transaction;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Database;
use RuntimeException;

class DatabaseProcessorTest extends TestCase
{
    private string $dbName = 'money_tracker_test';
    private Database $database;
    private DatabaseTransactionRepository $repositoryTransactions;
    private DatabaseAccountRepository $repositoryAccounts;
    private TransactionProcessor $processor;
    private DatabaseTransactionRepositoryErrorSave $repositoryTransactionSaveError;
    private TransactionProcessor $errorProcessor ;

    protected function setUp():void
    {
        parent::setUp();
        $this->database = new Database($this->dbName);
        $this->database->getConnection()->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->database->getConnection()->exec('TRUNCATE TABLE transactions');
        $this->database->getConnection()->exec('TRUNCATE TABLE accounts');
        $this->database->getConnection()->exec('SET FOREIGN_KEY_CHECKS = 1');
        $this->repositoryTransactions = new DatabaseTransactionRepository($this->database);
        $this->repositoryAccounts = new DatabaseAccountRepository($this->database);
        $this->repositoryTransactionSaveError = new DatabaseTransactionRepositoryErrorSave();
        $this->processor = new TransactionProcessor($this->repositoryTransactions, $this->repositoryAccounts, $this->database);
        $this->errorProcessor = new TransactionProcessor($this->repositoryTransactionSaveError, $this->repositoryAccounts, $this->database);
    }

    public function testProcessTransactionDepositSuccess():void
    {
        $idAccount = $this->repositoryAccounts->createAccount("test", "RUB");
        $account = new Account("test", 0.0, "RUB", $idAccount);
        $transaction = new Transaction(TransactionType::INCOME, 999.0, "salary");
        $idTransaction = $this->processor->processTransaction($transaction, $account);
        $this->assertSame(999.0, $this->repositoryAccounts->getAccountByIdOrFail($idAccount)->getBalance());
        $this->assertSame(TransactionType::INCOME, $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getType());
        $this->assertSame(999.0, $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getAmount());
        $this->assertSame("salary", $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getCategory());


    }

    public function testProcessTransactionWithdrawSuccess():void
    {
        $idAccount = $this->repositoryAccounts->createAccount("test", "RUB");
        $account = new Account("test", 999.0, "RUB", $idAccount);
        $transaction = new Transaction(TransactionType::EXPENSE, 99.0, "food");
        $idTransaction = $this->processor->processTransaction($transaction, $account);
        $this->assertSame(900.0, $this->repositoryAccounts->getAccountByIdOrFail($idAccount)->getBalance());
        $this->assertSame(TransactionType::EXPENSE, $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getType());
        $this->assertSame(99.0, $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getAmount());
        $this->assertSame("food", $this->repositoryTransactions->getTransactionByIdOrFail($idTransaction)->getCategory());


    }
    
    public function testProcessTransactionInsufficientBalance():void
    {
        $account = new Account("test", 0.0, "RUB", 99);
        $transaction = new Transaction(TransactionType::EXPENSE, 9999.9, "test", 99);
        $this->expectException(InvalidArgumentException::class);
        $this->processor->processTransaction($transaction, $account);
    }

    public function testProcessTransactionErrorForSaveAccountUnavailble():void
    {   $this->repositoryAccounts->createAccount("test", "RUB");
        $account = new Account("test", 999.0, "RUB", 999999999);
        $transaction = new Transaction(TransactionType::EXPENSE, 99.0, "food");
        $this->expectException(RuntimeException::class);
        $this->processor->processTransaction($transaction, $account);

    }

    public function testProcessTransactionErrorForSaveAccountFound():void
    {
        $idAccount = $this->repositoryAccounts->createAccount("test", "RUB");
        $this->repositoryAccounts->updateAccountBalance($idAccount, 999.0);
        $account = new Account("test", 999.9, "RUB", $idAccount);
        $transaction = new Transaction(TransactionType::EXPENSE, 99.0, "food");
        try {
            $this->errorProcessor->processTransaction($transaction, $account);
        } catch (RuntimeException $e) {
            $this->assertEquals("Искусственная ошибка сохранения", $e->getMessage());
        }
        $this->assertSame(999.0, $this->repositoryAccounts->getAccountByIdOrFail($idAccount)->getBalance());
    }

}