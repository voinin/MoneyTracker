<?php
namespace MoneyTracker\Tests\Integration;

use InvalidArgumentException;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Repositories\DatabaseTransactionRepository;
use MoneyTracker\Transaction;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use MoneyTracker\Repositories\DatabaseAccountRepository;
use MoneyTracker\Account;
use function PHPUnit\Framework\assertSame;

class DatabaseTransactionRepositoryTest extends TestCase
{
    private string $dbName = 'money_tracker_test';
    private Database $database;
    private DatabaseTransactionRepository $repositoryTransactions;
    private DatabaseAccountRepository $repositoryAccounts;

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
    }

    public function testConnection():void
    {
        $this->assertTrue($this->database->testConnection());
    }

    public function testCreateTransactionWithDatabaseRepository():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $transaction = new Transaction(TransactionType::EXPENSE, 2500.0, "food");
        $id = $this->repositoryTransactions->save($transaction, $account_id);
        $this->assertGreaterThan(0, $id);
        $transactionByRepostitory = $this->repositoryTransactions->getTransactionByIdOrFail($id);
        $this->assertNotNull($transactionByRepostitory);
        $this->assertSame(TransactionType::EXPENSE, $transactionByRepostitory->getType());
        $this->assertSame(2500.0, $transactionByRepostitory->getAmount());
        $this->assertSame("food", $transactionByRepostitory->getCategory());
    }

    public function testGetTransactionsByType():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 3300.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 9000.0, "car repair"), $account_id);
        $rows = $this->repositoryTransactions->getTransactionsByType(TransactionType::EXPENSE);
        $this->assertCount(2, $rows);
        $this->assertSame(TransactionType::EXPENSE, $rows[0]->getType());
        $this->assertSame(TransactionType::EXPENSE, $rows[1]->getType());
    }

    public function testGetTransactionByTypeReturnsEmptyArray():void
    {
        $this->assertSame([], $this->repositoryTransactions->getTransactionsByType(TransactionType::EXPENSE));
    }

    public function testGetTransactionsByCategory():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 2700.0, "food"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 33000, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 9000, "food"), $account_id);
        $rows = $this->repositoryTransactions->getTransactionsByCategory("food");
        $this->assertCount(2, $rows);
        $this->assertSame("food", $rows[0]->getCategory());
        $this->assertSame("food", $rows[1]->getCategory());
    }

    public function testGetTransactionByCategoryReturnsEmptyArray():void
    {
        $this->assertSame([], $this->repositoryTransactions->getTransactionsByCategory("food"));
    }

    public function testGetTransactionByIdOrFailReturnsTransaction():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $id = $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 50000, "salary"), $account_id);
        $transaction = $this->repositoryTransactions->getTransactionByIdOrFail($id);
        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function testGetTransactionByIdOrFailReturnsFail():void
    {
        $this->expectException(RuntimeException::class);
        $this->repositoryTransactions->getTransactionByIdOrFail(1);
    }

    public function testGetIncomeSum():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 50000.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5000.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 9000.0, "food"), $account_id);
        $this->assertSame(55000.0, $this->repositoryTransactions->getIncomeSum());
    }

    public function testGetIncomeSumReturnsZero():void
    {
        $this->assertSame(0.0, $this->repositoryTransactions->getIncomeSum());
    }

    public function testGetExpenseSum():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 50000.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5000.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 9000.0, "food"), $account_id);
        $this->assertSame(9000.0, $this->repositoryTransactions->getExpenseSum());
    }

    public function testGetExpenseSumReturnsZero():void
    {
        $this->assertSame(0.0, $this->repositoryTransactions->getExpenseSum());
    }

    public function testGetTransactionCountByType():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 500.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 3000.0, "food"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 700.0, "food"), $account_id);
        $this->assertSame(3, $this->repositoryTransactions->getTransactionsCountByType(TransactionType::INCOME));
        $this->assertSame(2, $this->repositoryTransactions->getTransactionsCountByType(TransactionType::EXPENSE));
    }

    public function testGetTransactionCountByTypeReturnsZero():void
    {
        $this->assertSame(0, $this->repositoryTransactions->getTransactionsCountByType(TransactionType::INCOME));
    }

    public function testGetLatestTransactions():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 500.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"), $account_id);
        $this->assertCount(2, $this->repositoryTransactions->getLatestTransactions(2));
    }

    public function testGetLatestTransactionsThrowsExceptionOnInvalidLimit():void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->repositoryTransactions->getLatestTransactions(0);
    }

    public function testGetLatestTransactionsReturnEmptyArray():void
    {
        $this->assertSame([], $this->repositoryTransactions->getLatestTransactions(2));
    }

    public function testGetTransactions():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"), $account_id);
        $transactions = $this->repositoryTransactions->getTransactions();

        $this->assertCount(3, $transactions);
        $this->assertSame(TransactionType::INCOME, $transactions[0]->getType());
        $this->assertSame(TransactionType::EXPENSE, $transactions[1]->getType());
        $this->assertSame(TransactionType::INCOME, $transactions[2]->getType());
    }

    public function testGetTransactionsArrayEmptyArray():void
    {
        $this->assertSame([], $this->repositoryTransactions->getTransactions());
    }

    public function testSave():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $incomeId = $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"), $account_id);
        $expenseId = $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"), $account_id);
        $income = $this->repositoryTransactions->getTransactionByIdOrFail($incomeId);
        $expense = $this->repositoryTransactions->getTransactionByIdOrFail($expenseId);
        $this->assertSame(TransactionType::INCOME, $income->getType());
        $this->assertSame(56700.0, $income->getAmount());
        $this->assertSame("salary", $income->getCategory());

        $this->assertSame(TransactionType::EXPENSE, $expense->getType());
        $this->assertSame(500.0, $expense->getAmount());
        $this->assertSame("food", $expense->getCategory());
    }

    public function testGetTransactionsCount():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 56700.0, "salary"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 500.0, "food"), $account_id);
        $this->repositoryTransactions->save(new Transaction(TransactionType::INCOME, 5700.0, "salary"), $account_id);
        assertSame(3, $this->repositoryTransactions->getTransactionsCount());
    }

    public function testGetTransactionsCountReturnsZero():void
    {
        $this->assertSame(0, $this->repositoryTransactions->getTransactionsCount());
    }

    public function testDeleteReturnsTrue():void
    {
        $account_id = $this->repositoryAccounts->createAccount("MainAccount", "RUB");
        $this->repositoryAccounts->updateAccountBalance($account_id, 9999999.0);
        $id = $this->repositoryTransactions->save(new Transaction(TransactionType::EXPENSE, 100.0, "food"), $account_id);
        $this->assertTrue($this->repositoryTransactions->delete($id));
        $this->assertSame(0, $this->repositoryTransactions->getTransactionsCount());
    }

    public function testDeleteReturnsFalseWhenIdDoesNotExist():void
    {
        $this->assertFalse($this->repositoryTransactions->delete(9999));
    }   

}