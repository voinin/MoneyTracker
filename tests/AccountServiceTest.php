<?php
namespace MoneyTracker\Tests\Integration;

use MoneyTracker\Repositories\TransactionRepository;
use MoneyTracker\Repositories\TransactionRepositoryInterface;
use MoneyTracker\Services\AccountService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
    
    public function testGetBalanceThrowsExceptionWhenRepositoryFails()
    {
        $repository = $this->createMock(TransactionRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('getIncomeSum')
            ->willThrowException(new RuntimeException());
        $service = new AccountService($repository);
        $this->expectException(RuntimeException::class);
        $service->getBalance();
    }

    public function testGetBalanceReturnsZero()
    {
        $repository = $this->createMock(TransactionRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('getIncomeSum')
            ->willReturn(0.0);
        $repository
            ->expects($this->once())
            ->method('getExpenseSum')
            ->willReturn(0.0);

        $service = new AccountService($repository);
        $this->assertSame(0.0, $service->getBalance());
    }
}