<?php

namespace MoneyTracker;

interface AccountInterface
{
    public function deposit(float $amount):bool;
    public function withdraw(float $amount):bool;
    public function getCurrency():string;
    public function getBalance():float;
}