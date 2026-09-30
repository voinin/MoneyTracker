<?php
namespace MoneyTracker\Tests\Integration;
use PHPUnit\Framework\TestCase;
use MoneyTracker\Database;
use MoneyTracker\Repositories\DatabaseAccountRepository;
use MoneyTracker\Account;
use RuntimeException;

class DatabaseAccountRepositoryTest extends TestCase
{   
    private string $dbName = 'money_tracker_test';
    private Database $database;
    private DatabaseAccountRepository $repositoryTransactions;

    protected function setUp():void
    {
        parent::setUp();
        $this->database = new Database($this->dbName);
        $this->database->getConnection()->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->database->getConnection()->exec('TRUNCATE TABLE accounts');
        $this->database->getConnection()->exec('SET FOREIGN_KEY_CHECKS = 1');
        $this->repositoryTransactions = new DatabaseAccountRepository($this->database);
    }

    public function testConnection():void
    {
        $this->assertTrue($this->database->testConnection());
    }

    public function testCreateAccountSuccess():void
    {
        $this->assertSame(1, $this->repositoryTransactions->createAccount("Основная карта", "RUB"));
    }

    public function testGetAccountById():void
    {
        $id = $this->repositoryTransactions->createAccount("Основной счет", "RUB");
        $account = $this->repositoryTransactions->getAccountByIdOrFail($id);
        $this->assertInstanceOf(Account::class, $account);
    }

    public function testGetAccountByIdFail():void
    {
        $this->expectException(RuntimeException::class);
        $this->repositoryTransactions->getAccountByIdOrFail(999999);
    }

    public function testUpdateAccountBalance():void
    {
        $id = $this->repositoryTransactions->createAccount("Основной счет", "RUB");
        $this->repositoryTransactions->updateAccountBalance($id, 50000.0);
        $this->assertSame(50000.0, $this->repositoryTransactions->getAccountByIdOrFail($id)->getBalance());
    }

    public function testUpdateAccountBalanceThrowException():void
    {
        $this->expectException(RuntimeException::class);
        $this->repositoryTransactions->updateAccountBalance(999999, 50000.0);
    }
}