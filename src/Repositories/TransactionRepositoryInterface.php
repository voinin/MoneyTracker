<?php

namespace MoneyTracker\Repositories;
use MoneyTracker\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;
}