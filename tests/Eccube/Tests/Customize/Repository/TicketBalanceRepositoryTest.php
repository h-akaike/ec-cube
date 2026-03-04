<?php

namespace Eccube\Tests\Customize\Repository;

use Customize\Entity\TicketBalance;
use Customize\Repository\TicketBalanceRepository;
use Eccube\Entity\Customer;
use Eccube\Tests\EccubeTestCase;

class TicketBalanceRepositoryTest extends EccubeTestCase
{
    /** @var TicketBalanceRepository */
    protected $ticketBalanceRepository;

    /** @var Customer */
    protected $Customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketBalanceRepository = $this->entityManager->getRepository(TicketBalance::class);
        $this->Customer = $this->createCustomer();
    }

    public function testSaveAndFind()
    {
        $balance = $this->createTicketBalance($this->Customer, 20.0);

        $found = $this->ticketBalanceRepository->find($balance->getId());
        $this->assertNotNull($found);
        $this->assertEquals(20.0, $found->getTotalHours());
        $this->assertEquals(0, $found->getUsedHours());
        $this->assertSame($this->Customer->getId(), $found->getCustomer()->getId());
    }

    public function testDelete()
    {
        $balance = $this->createTicketBalance($this->Customer, 10.0);
        $id = $balance->getId();

        $this->ticketBalanceRepository->delete($balance);
        $this->entityManager->flush();

        $this->assertNull($this->ticketBalanceRepository->find($id));
    }

    public function testGetActiveBalancesByCustomer()
    {
        // 有効な残高 2 件
        $this->createTicketBalance($this->Customer, 10.0, '+3 months');
        $this->createTicketBalance($this->Customer, 20.0, '+6 months');

        // 期限切れ 1 件
        $this->createTicketBalance($this->Customer, 5.0, '-1 day');

        // 全消化済み 1 件
        $allUsed = $this->createTicketBalance($this->Customer, 10.0, '+3 months');
        $allUsed->setUsedHours(10.0);
        $this->entityManager->flush();

        $balances = $this->ticketBalanceRepository->getActiveBalancesByCustomer($this->Customer);

        $this->assertCount(2, $balances);
    }

    public function testGetTotalRemainingHoursByCustomer()
    {
        $this->createTicketBalance($this->Customer, 20.0, '+3 months');
        $b2 = $this->createTicketBalance($this->Customer, 10.0, '+6 months');
        $b2->setUsedHours(3.0);
        $this->entityManager->flush();

        // 期限切れは含まない
        $this->createTicketBalance($this->Customer, 50.0, '-1 day');

        $total = $this->ticketBalanceRepository->getTotalRemainingHoursByCustomer($this->Customer);

        // 20.0 + (10.0 - 3.0) = 27.0
        $this->assertEquals(27.0, $total);
    }

    private function createTicketBalance(Customer $customer, float $totalHours, string $expiresAt = '+6 months'): TicketBalance
    {
        $balance = new TicketBalance();
        $balance->setCustomer($customer);
        $balance->setTotalHours($totalHours);
        $balance->setUsedHours(0);
        $balance->setExpiresAt(new \DateTime($expiresAt));
        $balance->setCreateDate(new \DateTime());
        $balance->setUpdateDate(new \DateTime());

        $this->ticketBalanceRepository->save($balance);

        return $balance;
    }
}
