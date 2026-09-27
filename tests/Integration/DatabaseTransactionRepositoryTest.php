<?php
namespace MoneyTracker\Tests\Integration;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Transaction;
use PHPUnit\Framework\TestCase;

class DatabaseTransactionRepositoryTest extends TestCase
{
    private string $dbName = 'money_tracker_test';
    private Database $database;

    protected function setUp():void
    {
        parent::setUp();
        $this->database = new Database($this->dbName);
        $this->database->getConnection()->exec('TRUNCATE TABLE transactions');
    }

    public function testConnection()
    {
        $this->assertTrue($this->database->testConnection());
    }

    public function testCreateTransactionWithDatabaseRepository()
    {
        $repository = new DatabaseTransactionRepository($this->database);
        $transaction =  new Transaction(TransactionType::EXPENSE, 2500.0, "food");
        $id = $repository->save($transaction);
        $this->assertGreaterThan(0, $id);
        $transactionByRepostitory = $repository->getTransactionById($id);
        $this->assertNotNull($transactionByRepostitory);
        $this->assertSame(TransactionType::EXPENSE, $transactionByRepostitory->getType());
        $this->assertSame(2500.0, $transactionByRepostitory->getAmount());
        $this->assertSame("food", $transactionByRepostitory->getCategory());
    }

    public function testGetTransactionsByType()
    {
        $repository = new DatabaseTransactionRepository($this->database);
        $repository->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"));
        $repository->save(new Transaction(TransactionType::INCOME, 33000, "salary"));
        $repository->save(new Transaction(TransactionType::EXPENSE, 9000, "car repair"));
        $rows = $repository->getTransactionsByType(TransactionType::EXPENSE);
        $this->assertCount(2, $rows);
        $this->assertSame(TransactionType::EXPENSE, $rows[0]->getType());
        $this->assertSame(TransactionType::EXPENSE, $rows[1]->getType());
    }

    public function testGetTransactionsByCategory()
    {
        $repository = new DatabaseTransactionRepository($this->database);
        $repository->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"));
        $repository->save(new Transaction(TransactionType::INCOME, 33000, "salary"));
        $repository->save(new Transaction(TransactionType::EXPENSE, 9000, "food"));
        $rows = $repository->getTransactionsByCategory("food");
        $this->assertCount(2, $rows);
        $this->assertSame("food", $rows[0]->getCategory());
        $this->assertSame("food", $rows[1]->getCategory());
    }
}