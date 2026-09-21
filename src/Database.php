<?php 

namespace MoneyTracker;

use PDO;

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
        return (int) $transactionCount;
    }
}
