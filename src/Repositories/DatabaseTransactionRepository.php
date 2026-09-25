<?php
namespace MoneyTracker\Repositories;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Transaction;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use RuntimeException;

class DatabaseTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private Database $database) {}

    #[\Override]
    public function save(Transaction $transaction): int
    {
        $sql = 'INSERT INTO transactions (type, amount, category)
        VALUES (:type, :amount, :category)';

        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':type' => $transaction->getType()->value,
            ':amount' => $transaction->getAmount(),
            ':category' => $transaction->getCategory()
        ]);

        return (int) $this->database->getConnection()->lastInsertId();
    }

    private function createTransactionsFromArrayOfRows(array $rows):array
    {
        $transactions = [];
        foreach ($rows as $row) {
            $transactions[] = $this->createTransactionFromRow($row);
        }
        return $transactions;
    }

    public function getTransactionsCount():int
    {
        $transactionCount = $this->database->getConnection()->query("SELECT COUNT(*) FROM transactions;");
        return (int) $transactionCount->fetchColumn();
    }

    public function getTransactions():array
    {
        $stmt = $this->database->getConnection()->query("SELECT id, type, amount, category, created_at FROM transactions ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();
        return $this->createTransactionsFromArrayOfRows($rows);
    }

    public function getIncomeCount():int
    {
        $incomeCount = $this->database->getConnection()->prepare("SELECT COUNT(*) FROM transactions WHERE type = 'income'");
        $incomeCount->execute();
        return (int) $incomeCount->fetchColumn();
    }

    public function getTransactionsByType(TransactionType $type):array
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, type, amount, category, created_at FROM transactions WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute([':type' => $type->value]);
        $rows = $stmt->fetchAll();
        return $this->createTransactionsFromArrayOfRows($rows);
    }

    public function createTransactionFromRow(array $row):Transaction
    {
        return new Transaction(TransactionType::from($row['type']), (float) $row['amount'], $row['category']);
    }
    
    public function getTransactionById(int $id): ?Transaction
    {
        $stmt = $this->database->getConnection()->prepare("SELECT type, amount, category FROM transactions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $transaction = $stmt->fetch();
        if ($transaction !== false) {
            return $this->createTransactionFromRow($transaction);

        }
        return null;
    }

    public function getExpenseSum():float
    {
        $stmt = $this->database->getConnection()->prepare ("SELECT SUM(amount) FROM transactions WHERE type = :type");
        $stmt->execute([':type' => TransactionType::EXPENSE->value]);
        $expenseSum = $stmt->fetchColumn();
         if ($expenseSum !== null) {
          return (float) $expenseSum;
        }
        return 0.0; 
    }

    public function getIncomeSum():float
    {
        $stmt = $this->database->getConnection()->prepare ("SELECT SUM(amount) FROM transactions WHERE type = :type");
        $stmt->execute([':type' => TransactionType::INCOME->value]);
        $incomeSum = $stmt->fetchColumn();
        if ($incomeSum !== null) {
            return (float) $incomeSum;
        }
        return 0.0;
    }

    public function delete(int $id):bool
    {
        $stmt=$this->database->getConnection()->prepare("DELETE FROM transactions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getTransactionByIdOrFail(int $id):Transaction
    {
        $transaction = $this->getTransactionById($id);
        if ($transaction !== null) {
            return $transaction;
        }
        throw new RuntimeException("Ошибка репозитория! Такой транзакции не существует!");
    }
}