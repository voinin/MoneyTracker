<?php

namespace MoneyTracker;

use InvalidArgumentException;

class Account implements AccountInterface
{
    private string $name;
    private float $balance;
    private string $currency;

    function __construct(string $name, float $balance, string $currency, private ?int $id = null)
    {
        $this->name = $name;
        $this->balance = $balance;
        $this->currency = $currency;
        $this->id = $id;
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

    public function getId():?int
    {
        return $this->id;
    }

    public function setBalance(float $newBalance):bool
    {
        if ($newBalance < 0) {
            throw new InvalidArgumentException("Ошибка! Баланс не может быть отрицательным!");
        }

        $this->balance = (float) $newBalance;
        return true;
    }

}