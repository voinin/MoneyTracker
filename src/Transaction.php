<?php

namespace MoneyTracker;

use InvalidArgumentException;
use MoneyTracker\Enums\TransactionType;

class Transaction
{   private TransactionType $type;
    private float $amount;
    private string $category;
    function __construct(TransactionType $type, float $amount, string $category, private ?int $id = null)
    {
        $this -> type = $type;
        if ($amount > 0) {
            $this -> amount = $amount;
        } else {
            throw new InvalidArgumentException("Введена некорректная сумма!");
        }
        $this -> category = $category;
    }

    public function getType()
    {
        return $this->type;
    }
    
     public function isExpense():bool
    {
        return $this->type === TransactionType::EXPENSE;
    }

    public function isIncome():bool
    {
        return $this->type === TransactionType::INCOME;
    }

    public function getAmount():float
    {
        return $this->amount;
    }

    public function getCategory():string
    {
        return $this->category;
    }

    public function getId():?int
    {
        return $this->id;
    }
}


