<?php 

namespace MoneyTracker\Services;
use MoneyTracker\AccountInterface;
use MoneyTracker\Account;
use InvalidArgumentException;
class TransferService
{
    private function validateCurrency(AccountInterface $from, AccountInterface $to):bool
    {
        if ($from->getCurrency() === $to->getCurrency()) {
            return true;
        } else {
            return false;
        }
    }

    private function validateAmount(float $amount):bool
    {
        if ($amount > 0) {
            return true; 
        } else {
            return false;
        }
    }
    public function transfer(AccountInterface $from, AccountInterface $to, float $amount):bool
    {
        if (!$this->validateAmount($amount)) {
            throw new \InvalidArgumentException("Введена некорректная сумма!");
        }

        if (!$this->validateCurrency($from, $to)) {
            throw new \InvalidArgumentException("Разные валюты, перевод невозможен!");

        }
        if (!$from->withdraw($amount)) {
            throw new \InvalidArgumentException("Списание не было выполнено, перевод невозможен!");
        }

        if (!$to->deposit($amount)) {
            throw new \InvalidArgumentException("Списание прошло успешно, но поступление не было выполнено, перевод не завершен!");
        }

        return true;
    }
}