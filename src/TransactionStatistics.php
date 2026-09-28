<?php
namespace MoneyTracker;

class TransactionStatistics
{
    public function calculateIncome(array $transactions):float
    {
        $sum = 0.0;
        foreach ($transactions as $transaction) {
            if ($transaction->isIncome()) {
                $sum += $transaction->getAmount();
            }
        }
        return $sum;
    }

    public function calculateExpense(array $transactions):float
    {
        $sum = 0.0;
        foreach ($transactions as $transaction) {
            if ($transaction->isExpense()) {
                $sum += $transaction->getAmount();
            }
        }
        return $sum;
    }

    public function calculateBalance(array $transactions):float
    {
        return $this->calculateIncome($transactions) - $this->calculateExpense($transactions);
    }
}