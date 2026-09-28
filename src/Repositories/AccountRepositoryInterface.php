<?php
namespace MoneyTracker\Repositories;

use MoneyTracker\AccountInterface;

interface AccountRepositoryInterface
{
    public function createAccount(string $name, string $currency):int;
    public function getAccountByIdOrFail(int $id):AccountInterface;
    public function updateAccountBalance(int $id, float $balance):void;
}
