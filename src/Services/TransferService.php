<?php 

namespace MoneyTracker\Services;
use MoneyTracker\AccountInterface;
use MoneyTracker\Account;
use InvalidArgumentException;
class TransferService
{
    private function currencyValidate(AccountInterface $from, AccountInterface $to):bool
    {
        if ($from->getCurrency() === $to->getCurrency()) {
            return true;
        } else {
            return false;
        }
    }
    public function transfer(AccountInterface $from, AccountInterface $to, float $amount):bool
    {
        if ($this->currencyValidate($from, $to)) {
            if ($from->withdraw($amount)) {
                return $to->deposit($amount);
            } else {
                throw new InvalidArgumentException("Списание не было выполнено, перевод невозможен!");
            }
        } else {
            throw new InvalidArgumentException("Разные валюты, перевод невозможен!");
        }
    }
}