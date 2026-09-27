<?php
namespace MoneyTracker\Tests\Integration;

use InvalidArgumentException;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Transaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function PHPUnit\Framework\assertSame;

class DatabaseTransactionRepositoryTest extends TestCase
{
    private string $dbName = 'money_tracker_test';
    private Database $database;
    private DatabaseTransactionRepository $repository;

    protected function setUp():void
    {
        parent::setUp();
        $this->database = new Database($this->dbName);
        $this->database->getConnection()->exec('TRUNCATE TABLE transactions');
        $this->repository = new DatabaseTransactionRepository($this->database);
    }

    public function testConnection():void
    {
        $this->assertTrue($this->database->testConnection());
    }

    public function testCreateTransactionWithDatabaseRepository():void
    {
        $transaction =  new Transaction(TransactionType::EXPENSE, 2500.0, "food");
        $id = $this->repository->save($transaction);
        $this->assertGreaterThan(0, $id);
        $transactionByRepostitory = $this->repository->getTransactionByIdOrFail($id);
        $this->assertNotNull($transactionByRepostitory);
        $this->assertSame(TransactionType::EXPENSE, $transactionByRepostitory->getType());
        $this->assertSame(2500.0, $transactionByRepostitory->getAmount());
        $this->assertSame("food", $transactionByRepostitory->getCategory());
    }

    public function testGetTransactionsByType():void
    {
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 3300.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 9000.0, "car repair"));
        $rows = $this->repository->getTransactionsByType(TransactionType::EXPENSE);
        $this->assertCount(2, $rows);
        $this->assertSame(TransactionType::EXPENSE, $rows[0]->getType());
        $this->assertSame(TransactionType::EXPENSE, $rows[1]->getType());
    }

    public function testGetTransactionByTypeReturnsEmptyArray():void
    {
        $this->assertSame([], $this->repository->getTransactionsByType(TransactionType::EXPENSE));
    }

    public function testGetTransactionsByCategory():void
    {
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 33000, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 9000, "food"));
        $rows = $this->repository->getTransactionsByCategory("food");
        $this->assertCount(2, $rows);
        $this->assertSame("food", $rows[0]->getCategory());
        $this->assertSame("food", $rows[1]->getCategory());
    }

    public function testGetTransactionByCategoryReturnsEmptyArray():void
    {
        $this->assertSame([], $this->repository->getTransactionsByCategory("food"));
    }

    public function testGetTransactionByIdOrFailReturnsTransaction():void
    {
        $id = $this->repository->save(new Transaction(TransactionType::INCOME, 50000, "salary"));
        $transaction = $this->repository->getTransactionByIdOrFail($id);
        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function testGetTransactionByIdOrFailReturnsFail():void
    {
        $this->expectException(RuntimeException::class);
        $this->repository->getTransactionByIdOrFail(1);
    }

    public function testGetIncomeSum():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 50000.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5000.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 9000.0, "food"));
        $this->assertSame(55000.0, $this->repository->getIncomeSum());
    }

    public function testGetIncomeSumReturnsZero():void
    {
        $this->assertSame(0.0, $this->repository->getIncomeSum());
    }

    public function testGetExpenseSum():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 50000.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5000.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 9000.0, "food"));
        $this->assertSame(9000.0, $this->repository->getExpenseSum());
    }

    public function testGetExpenseSumReturnsZero():void
    {
        $this->assertSame(0.0, $this->repository->getExpenseSum());
    }

    public function testGetTransactionCountByType():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 500.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 3000.0, "food"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 700.0, "food"));
        $this->assertSame(3, $this->repository->getTransactionsCountByType(TransactionType::INCOME));
        $this->assertSame(2, $this->repository->getTransactionsCountByType(TransactionType::EXPENSE));
    }

    public function testGetTransactionCountByTypeReturnsZero():void
    {
        $this->assertSame(0, $this->repository->getTransactionsCountByType(TransactionType::INCOME));
    }

    public function testGetLatestTransactions():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 500.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"));
        $this->assertCount(2, $this->repository->getLatestTransactions(2));
    }

    public function testGetLatestTransactionsThrowsExceptionOnInvalidLimit():void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->repository->getLatestTransactions(0);
    }

    public function testGetLatestTransactionsReturnEmptyArray():void
    {
        $this->assertSame([], $this->repository->getLatestTransactions(2));
    }

    public function testGetTransactions():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"));
        $transactions = $this->repository->getTransactions();

        $this->assertCount(3, $transactions);
        $this->assertSame(TransactionType::INCOME, $transactions[0]->getType());
        $this->assertSame(TransactionType::EXPENSE, $transactions[1]->getType());
        $this->assertSame(TransactionType::INCOME, $transactions[2]->getType());
    }

    public function testGetTransactionsArrayEmptyArray():void
    {
        $this->assertSame([], $this->repository->getTransactions());
    }

    public function testSave():void
    {
        $incomeId = $this->repository->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"));
        $expenseId = $this->repository->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"));
        $income = $this->repository->getTransactionByIdOrFail($incomeId);
        $expense = $this->repository->getTransactionByIdOrFail($expenseId);
        $this->assertSame(TransactionType::INCOME, $income->getType());
        $this->assertSame(56700.0, $income->getAmount());
        $this->assertSame("salary", $income->getCategory());

        $this->assertSame(TransactionType::EXPENSE, $expense->getType());
        $this->assertSame(500.0, $expense->getAmount());
        $this->assertSame("food", $expense->getCategory());
    }

    public function testGetTransactionsCount():void
    {
        $this->repository->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"));
        $this->repository->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"));
        $this->repository->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"));
        assertSame(3, $this->repository->getTransactionsCount());
    }

    public function testGetTransactionsCountReturnsZero():void
    {
        $this->assertSame(0, $this->repository->getTransactionsCount());
    }

    public function testDeleteReturnsTrue():void
    {
        $id = $this->repository->save(new Transaction(TransactionType::EXPENSE, 100.0, "food"));
        $this->assertTrue($this->repository->delete($id));
        $this->assertSame(0, $this->repository->getTransactionsCount());
    }

    public function testDeleteReturnsFalseWhenIdDoesNotExist():void
    {
        $this->assertFalse($this->repository->delete(9999));
    }   

}