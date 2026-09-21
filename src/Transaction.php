<?php

namespace MoneyTracker;

use MoneyTracker\Enums\TransactionType;

class Transaction
{   private TransactionType $type;
    private float $amount;
    private string $category;
    function __construct(TransactionType $type, float $amount, string $category)
    {
        $this -> type = $type;
        $this -> amount = $amount;
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

    public function getInfo():string
    {
        if ($this->type === "income") {
            return "Доход: $this->amount ($this->category)";
        } elseif ($this->type === "expense") {
            return "Расход: $this->amount ($this->category)";
        } else {
            return "Неизвестный тип операции";
        }
    }

    public function getCategory():string
    {
        return $this->category;
    }
}


