<?php

namespace MoneyTracker;

class TransactionStatistics
{
    public function calculateIncome(array $transactions):float
    {
        $totalIncome = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->isIncome()) {
                $totalIncome += $transaction->getAmount();
            }
        }
        return $totalIncome;
    }
}