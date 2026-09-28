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
    private DatabaseAccountRepository $repository;

    protected function setUp():void
    {
        parent::setUp();
        $this->database = new Database($this->dbName);
        $this->database->getConnection()->exec('TRUNCATE TABLE accounts');
        $this->repository = new DatabaseAccountRepository($this->database);
    }

    public function testConnection():void
    {
        $this->assertTrue($this->database->testConnection());
    }

    public function testCreateAccountSuccess():void
    {
        $this->assertSame(1, $this->repository->createAccount("Основная карта", "RUB"));
    }

    public function testGetAccountById():void
    {
        $id = $this->repository->createAccount("Основной счет", "RUB");
        $account = $this->repository->getAccountByIdOrFail($id);
        $this->assertInstanceOf(Account::class, $account);
    }

    public function testGetAccountByIdFail():void
    {
        $this->expectException(RuntimeException::class);
        $this->repository->getAccountByIdOrFail(999999);
    }

    public function testUpdateAccountBalance():void
    {
        $id = $this->repository->createAccount("Основной счет", "RUB");
        $this->repository->updateAccountBalance($id, 50000.0);
        $this->assertSame(50000.0, $this->repository->getAccountByIdOrFail($id)->getBalance());
    }

    public function testUpdateAccountBalanceThrowException():void
    {
        $this->expectException(RuntimeException::class);
        $this->repository->updateAccountBalance(999999, 50000.0);
    }
}