<?php 

namespace MoneyTracker;

use MoneyTracker\Enums\TransactionType;
use PDO;
use PhpParser\Node\Expr\FuncCall;

class Database
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname=money_tracker;charset=utf8mb4',
            'moneytracker',
            'moneytracker'
        );

        $this->connection->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function testConnection():bool
    {
        $connectionResult = $this->connection->query('SELECT 1');
        if ($connectionResult) {
            return true;
        } else {
            return false;
        }
    }

    public function getTransactionCount():int
    {
        $transactionCount = $this->connection->query("SELECT COUNT(*) FROM transactions;");
        return (int) $transactionCount->fetchColumn();
    }

    public function getTransactions():array
    {
        $stmt = $this->connection->query("SELECT id, type, amount, category, created_at FROM transactions ORDER BY created_at DESC");
        $transactions = $stmt->fetchAll();
        return $transactions;
    }

    public function getIncomeCount():int
    {
        $incomeCount = $this->connection->prepare("SELECT COUNT(*) FROM transactions WHERE type = 'income'");
        $incomeCount->execute();
        return (int) $incomeCount->fetchColumn();
    }

    public function getTransactionsByType(string $type):array
    {
        $stmt = $this->connection->prepare("SELECT id, type, amount, category, created_at FROM transactions WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute([' :type' => $type]);
        $transactions = $stmt->fetchAll();
        return $transactions;
    }
}
