<?php
namespace MoneyTracker\Services;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
class AccountService
{
    public function __construct(private TransactionRepositoryInterface $repository){}
    public function getBalance():float
    {
        $balance = $this->repository->getIncomeSum() - $this->repository->getExpenseSum();
        return $balance;
    }
}