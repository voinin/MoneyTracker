<?php

use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Transaction;
use MoneyTracker\Database;
use MoneyTracker\Enums\TransactionType;

class DatabaseTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private Database $database) {}

    #[Override]
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

    public function getTransactionsCount():int
    {
        $transactionCount = $this->database->getConnection()->query("SELECT COUNT(*) FROM transactions;");
        return (int) $transactionCount->fetchColumn();
    }

    public function getTransactions():array
    {
        $stmt = $this->database->getConnection()->query("SELECT id, type, amount, category, created_at FROM transactions ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();
        $transactions = [];
        foreach ($rows as $row) {
            $transactions[] = $this->createTransactionFromRow($row);
        }
        return $transactions;
    }

    public function getIncomeCount():int
    {
        $incomeCount = $this->database->getConnection()->prepare("SELECT COUNT(*) FROM transactions WHERE type = 'income'");
        $incomeCount->execute();
        return (int) $incomeCount->fetchColumn();
    }

    public function getTransactionsByType(string $type):array
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, type, amount, category, created_at FROM transactions WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute([':type' => $type]);
        $transactions = $stmt->fetchAll();
        return $transactions;
    }

    public function createTransactionFromRow(array $row):Transaction
    {
        return new Transaction(TransactionType::from($row['type']), (float) $row['amount'], $row['category']);
    }
}