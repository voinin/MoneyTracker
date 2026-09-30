<?php
namespace MoneyTracker\Repositories;

use InvalidArgumentException;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Transaction;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;
use RuntimeException;

class DatabaseTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private Database $database) {}

    #[\Override]
    public function save(Transaction $transaction, int $account_id): int
    {
        $sql = 'INSERT INTO transactions (type, amount, category, account_id)
        VALUES (:type, :amount, :category, :account_id)';

        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':type' => $transaction->getType()->value,
            ':amount' => $transaction->getAmount(),
            ':category' => $transaction->getCategory(),
            ':account_id' => $account_id
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
        $count = $transactionCount->fetchColumn();
        if (!$count) {
            return 0;
        }
        return (int) $count;
    }

    public function getTransactions():array
    {
        $stmt = $this->database->getConnection()->query("SELECT id, type, amount, category, created_at FROM transactions ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();
        return $this->createTransactionsFromArrayOfRows($rows);
    }

    public function getTransactionsByType(TransactionType $type):array
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, type, amount, category, created_at FROM transactions WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute([':type' => $type->value]);
        $rows = $stmt->fetchAll();
        return $this->createTransactionsFromArrayOfRows($rows);
    }

    private function createTransactionFromRow(array $row):Transaction
    {
        return new Transaction(TransactionType::from($row['type']), (float) $row['amount'], $row['category'], $row['id']);
    }
    
    private function getTransactionById(int $id): ?Transaction
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, type, amount, category, created_at FROM transactions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $transaction = $stmt->fetch();
        if ($transaction !== false) {
            return $this->createTransactionFromRow($transaction);

        }
        return null;
    }

    private function getSumByType(TransactionType $type): float
    {
        $stmt = $this->database->getConnection()->prepare ("SELECT SUM(amount) FROM transactions WHERE type = :type");
        $stmt->execute([':type' => $type->value]);
        $sum = $stmt->fetchColumn();
         if ($sum !== null) {
          return (float) $sum;
        }
        return 0.0;
    }

    public function getExpenseSum():float
    {
        return $this->getSumByType(TransactionType::EXPENSE);
    }

    public function getIncomeSum():float
    {
        return $this->getSumByType(TransactionType::INCOME);
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

    public function getTransactionsCountByType(TransactionType $type):int
    {
        $stmt = $this->database->getConnection()->prepare("SELECT COUNT(*) FROM transactions WHERE type = :type");
        $stmt->execute([":type" => $type->value]);
        return (int) $stmt->fetchColumn();
    }

    public function getTransactionsByCategory(string $category):array
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, type, amount, category FROM transactions WHERE category = :category ORDER BY created_at DESC");
        $stmt->execute([":category" => $category]);
        $transactions = $stmt->fetchAll();
        return $this->createTransactionsFromArrayOfRows($transactions);
    }

    public function getLatestTransactions(int $limit): array
    {
        $limit = (int) $limit;
        if ($limit <= 0) {
            throw new InvalidArgumentException("Введено некорректное число!");
        }

        $stmt = $this->database->getConnection()->query("SELECT id, type, amount, category FROM transactions ORDER BY created_at DESC LIMIT $limit");
        return $this->createTransactionsFromArrayOfRows($stmt->fetchAll());
    }
}