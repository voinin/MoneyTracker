<?php

namespace MoneyTracker\Repositories;

use MoneyTracker\Enums\TransactionType;
use MoneyTracker\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction, int $account_id): int;
    public function getTransactionsCount():int;
    public function getTransactions():array;
    public function getTransactionsByType(TransactionType $type):array;
    public function getIncomeSum():float;
    public function getExpenseSum():float;
    public function getTransactionByIdOrFail(int $id):Transaction;
    public function delete(int $id):bool;
    public function getTransactionsCountByType(TransactionType $type): int;
    public function getTransactionsByCategory(string $category):array;
    public function getLatestTransactions(int $limit): array;
}