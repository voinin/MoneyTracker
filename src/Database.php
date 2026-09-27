<?php 

namespace MoneyTracker;

use MoneyTracker\Enums\TransactionType;
use PDO;
use PhpParser\Node\Expr\FuncCall;

class Database
{
    private PDO $connection;

    public function __construct(string $dbName = 'money_tracker')
    {
        $this->connection = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname='.$dbName.';charset=utf8mb4',
            'moneytracker',
            'moneytracker',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
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
}
