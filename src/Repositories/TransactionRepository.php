<?php
namespace MoneyTracker\Repositories;
use MoneyTracker\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction):void
    {
        echo "Транзакция сохранена!\n";
    }
}