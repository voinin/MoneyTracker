<?php

namespace MoneyTracker;

class Account implements AccountInterface
{
    private string $name;
    private float $balance;
    private string $currency;


    function __construct(string $name, float $balance, string $currency)
    {
        $this->name = $name;
        $this->balance = $balance;
        $this->currency = $currency;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getBalance():float
    {
        return $this->balance;
    }

    public function getCurrency():string
    {
        return $this->currency;
    }

    public function deposit(float $amount):bool
    {
        if ($amount <= 0){
            throw new \InvalidArgumentException("Введена некорректная сумма!");
        }
        $this->balance += $amount;
        return true;
    }

    public function withdraw(float $amount):bool
    {
        if ($amount <= 0){
            throw new \InvalidArgumentException("Введена некорректная сумма!");
        }
        if ($this->balance >= $amount){
            $this->balance -= $amount;
            return true;
        } else {
            throw new \InvalidArgumentException("Недостаточно средств!");
        }
    } 

}