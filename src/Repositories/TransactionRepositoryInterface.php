<?php

namespace MoneyTracker\Repositories;
use MoneyTracker\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): int;
    public function getTransactionsCount():int;
    public function getTransactions():array;
    public function getIncomeCount():int;
    public function getTransactionsByType(string $type):array;
}