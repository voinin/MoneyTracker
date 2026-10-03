<?php
namespace MoneyTracker\Tests\Integration;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Transaction;
use MoneyTracker\Enums\TransactionType;
use RuntimeException; 


class DatabaseTransactionRepositoryErrorSave implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction, int $account_id): int
    {
        throw new RuntimeException('Искусственная ошибка сохранения');
    }
    public function getTransactionsCount():int
    {
        throw new RuntimeException('Заглушка');
    }
    public function getTransactions():array
    {
        throw new RuntimeException('Заглушка');
    }
    public function getTransactionsByType(TransactionType $type):array
    {
        throw new RuntimeException('Заглушка');

    }
    public function getIncomeSum():float
    {
        throw new RuntimeException('Заглушка');

    }
    public function getExpenseSum():float
    {
        throw new RuntimeException('Заглушка');

    }
    public function getTransactionByIdOrFail(int $id):Transaction
    {
        throw new RuntimeException('Заглушка');

    }
    public function delete(int $id):bool
    {
        throw new RuntimeException('Заглушка');

    }
    public function getTransactionsCountByType(TransactionType $type): int
    {
        throw new RuntimeException('Заглушка');

    }
    public function getTransactionsByCategory(string $category):array
    {
        throw new RuntimeException('Заглушка');

    }
    public function getLatestTransactions(int $limit): array
    {
        throw new RuntimeException('Заглушка');

    }
}