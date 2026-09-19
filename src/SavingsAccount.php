<?php

namespace MoneyTracker;

class SavingsAccount extends Account 
{
    private const FACTOR = 100;
    private const MIN_BALANCE_AFTER_WITHDRAW  = 1000;
    public function addInterest(float $percent):void
    {
        if ($percent <= 0) {
           throw new \InvalidArgumentException("Введено неверное значение");
        }
        $interest = $percent / self::FACTOR;
        $this->deposit($this->getBalance() * $interest);    
        
    }
    #[\Override]
    public function withdraw(float $amount): bool
    {
        if ($amount <= 0){
            throw new \InvalidArgumentException("Введена некорректная сумма!");
        }
        if ($this->getBalance() - $amount >= self::MIN_BALANCE_AFTER_WITHDRAW ){
            return parent::withdraw($amount);
        } else {
            throw new \InvalidArgumentException("Недостаточно средств!");
        }
    }
}