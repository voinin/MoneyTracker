<?php

namespace MoneyTracker\Enums;

enum TransactionType: string
{
    case INCOME = "income";
    case EXPENSE = "expense";
}