<?php

namespace MoneyTracker\Repositories;
use MoneyTracker\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;
    public function getTransactiosCount():int;
    public function getTransaction():array;
    public function getIncome():int;
    public function getTransactionsByType(string $type):int;
}