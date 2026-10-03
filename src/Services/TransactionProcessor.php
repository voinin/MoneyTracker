<?php

namespace MoneyTracker\Services;

use InvalidArgumentException;
use MoneyTracker\Transaction;
use MoneyTracker\Account;
use MoneyTracker\Repositories\AccountRepositoryInterface;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Database;

class TransactionProcessor
{
    public function __construct(private TransactionRepositoryInterface $repositoryTransactions, private AccountRepositoryInterface $repositoryAccounts, private Database $database){}    

    public function processTransaction(Transaction $transaction, Account $account): int
    {
        $newBalance = 0.0;

        if ($transaction->isExpense()){
            if ($transaction->getAmount() > $account->getBalance()) {
                throw new InvalidArgumentException("Недостаточно средств для снятия со счета!");
            }
            $newBalance = $account->getBalance() - $transaction->getAmount();
        }

        if ($transaction->isIncome()){
            $newBalance = $account->getBalance() + $transaction->getAmount();
        }

        try 
        {
            $this->database->getConnection()->beginTransaction();
            $this->repositoryAccounts->updateAccountBalance($account->getId(), $newBalance);
            $id = $this->repositoryTransactions->save($transaction, $account->getId());

            $this->database->getConnection()->commit();
            $account->setBalance($newBalance);
            return $id;

        } catch (\Throwable $e)
        
        {
            if ($this->database->getConnection()->inTransaction()) {
                $this->database->getConnection()->rollBack();
            }
            throw $e;
        }
    }
}