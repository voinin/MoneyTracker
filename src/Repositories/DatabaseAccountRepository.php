<?php
namespace MoneyTracker\Repositories;

use MoneyTracker\AccountInterface;
use MoneyTracker\Account;
use MoneyTracker\Database;
use Override;
use RuntimeException;

class DatabaseAccountRepository implements AccountRepositoryInterface
{
    public function __construct(private Database $database){}
    
    #[Override]
    public function createAccount(string $name, string $currency): int
    {
        $stmt = $this->database->getConnection()->prepare("INSERT INTO accounts (name, balance, currency) VALUES (:name, 0, :currency)");
        $stmt->execute([
            ':name' => $name, 
            ':currency' => $currency]);
        
        return (int) $this->database->getConnection()->lastInsertId();
    }
    
    private function createAccountFromRow(array $row):AccountInterface
    {
        return new Account($row['name'], $row['balance'], $row['currency']);
    }

    private function getAccountById(int $id): ?Account
    {
        $stmt = $this->database->getConnection()->prepare("SELECT id, name, balance, currency FROM accounts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $account = $stmt->fetch();
        if ($account !== false) {
            return $this->createAccountFromRow($account);
        }
        return null;
    }
    #[Override]
    public function getAccountByIdOrFail(int $id): AccountInterface
    {
        $account = $this->getAccountById($id);
        if ($account !== null) {
            return $account;
        }
        throw new RuntimeException("Ошибка репозитория! Такого счета не существует!");
    }

    #[Override]
    public function updateAccountBalance(int $id, float $balance): void
    {
        if ($this->getAccountById($id) === null) {
            throw new RuntimeException("Ошибка! Аккаунта c таким id не существует!");
        }
        $stmt = $this->database->getConnection()->prepare("UPDATE accounts SET balance = :balance WHERE id = :id");
        $stmt->execute(['balance' => $balance, 'id' => $id]);
    }
}