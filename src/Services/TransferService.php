<?php 

namespace MoneyTracker\Services;
use MoneyTracker\AccountInterface;
use MoneyTracker\Account;
use InvalidArgumentException;
class TransferService
{
    public function transfer(AccountInterface $from, AccountInterface $to, float $amount):bool
    {
        if ($from->getCurrency() === $to->getCurrency()) {
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