<?php

namespace MoneyTracker\Services;

use MoneyTracker\Transaction;
use MoneyTracker\Account;
use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Repositories\TransactionRepositoryInterface;

class TransactionProcessor
{
    public function __construct(private TransactionRepositoryInterface $repository){}    

    public function processTransaction(Transaction $transaction, Account $account): bool
    {
        if ($transaction->isExpense()) {
            $account->withdraw($transaction->getAmount());
        } elseif ($transaction->isIncome()) {
            $account->deposit($transaction->getAmount());
        }
        $this->repository->save($transaction);
        return true;
    }
}