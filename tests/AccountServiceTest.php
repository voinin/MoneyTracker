<?php
namespace MoneyTracker\Tests;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Services\AccountService;
use PHPUnit\Framework\TestCase;

class AccountServiceTest extends TestCase
{
    public function testGetBalance()
    {
        $repository = $this->createMock(TransactionRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('getIncomeSum')
            ->willReturn(50000.0);
        $repository
            ->expects($this->once())
            ->method('getExpenseSum')
            ->willReturn(7500.0);

        $service = new AccountService($repository);
        $this->assertSame(42500.0, $service->getBalance());
    }
}