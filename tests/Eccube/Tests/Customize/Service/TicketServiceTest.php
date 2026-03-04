<?php

namespace Eccube\Tests\Customize\Service;

use Customize\Entity\TicketBalance;
use Customize\Entity\TicketUsage;
use Customize\Repository\TicketBalanceRepository;
use Customize\Repository\TicketUsageRepository;
use Customize\Service\TicketService;
use Eccube\Entity\Customer;
use Eccube\Tests\EccubeTestCase;

class TicketServiceTest extends EccubeTestCase
{
    /** @var TicketService */
    protected $ticketService;

    /** @var TicketBalanceRepository */
    protected $ticketBalanceRepository;

    /** @var TicketUsageRepository */
    protected $ticketUsageRepository;

    /** @var Customer */
    protected $Customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketBalanceRepository = $this->entityManager->getRepository(TicketBalance::class);
        $this->ticketUsageRepository = $this->entityManager->getRepository(TicketUsage::class);
        $this->ticketService = static::getContainer()->get(TicketService::class);
        $this->Customer = $this->createCustomer();
    }

    public function testAddBalance()
    {
        $balance = $this->ticketService->addBalance($this->Customer, 20.0);

        $this->assertInstanceOf(TicketBalance::class, $balance);
        $this->assertEquals(20.0, $balance->getTotalHours());
        $this->assertEquals(0, $balance->getUsedHours());
        $this->assertSame($this->Customer->getId(), $balance->getCustomer()->getId());
        $this->assertNotNull($balance->getExpiresAt());
        $this->assertNotNull($balance->getId());
    }

    public function testGetRemainingHours()
    {
        $this->ticketService->addBalance($this->Customer, 20.0);
        $this->ticketService->addBalance($this->Customer, 10.0);

        $total = $this->ticketService->getRemainingHours($this->Customer);
        $this->assertEquals(30.0, $total);
    }

    public function testGetRemainingHoursWithNoBalances()
    {
        $total = $this->ticketService->getRemainingHours($this->Customer);
        $this->assertEquals(0, $total);
    }

    public function testConsumeTicketSingleBalance()
    {
        $this->ticketService->addBalance($this->Customer, 20.0);

        $usage = $this->ticketService->consumeTicket(
            $this->Customer,
            3.0,
            'Web制作',
            'トップページ作成',
            new \DateTime('2026-03-01'),
            '田中太郎'
        );

        $this->assertInstanceOf(TicketUsage::class, $usage);
        $this->assertEquals(3.0, $usage->getHours());
        $this->assertEquals('Web制作', $usage->getServiceType());

        $remaining = $this->ticketService->getRemainingHours($this->Customer);
        $this->assertEquals(17.0, $remaining);
    }

    public function testConsumeTicketFIFO()
    {
        // 古い残高（5h）を先に作成
        $b1 = $this->ticketService->addBalance($this->Customer, 5.0);
        // 新しい残高（20h）を後に作成
        $b2 = $this->ticketService->addBalance($this->Customer, 20.0);

        // 7h 消化 → 古い方 5h を全消化 + 新しい方から 2h
        $this->ticketService->consumeTicket(
            $this->Customer,
            7.0,
            'デザイン',
            'バナー作成',
            new \DateTime('2026-03-01'),
            '鈴木一郎'
        );

        $this->entityManager->refresh($b1);
        $this->entityManager->refresh($b2);

        $this->assertEquals(5.0, $b1->getUsedHours()); // 全消化
        $this->assertEquals(2.0, $b2->getUsedHours()); // 2h 消化

        $remaining = $this->ticketService->getRemainingHours($this->Customer);
        $this->assertEquals(18.0, $remaining);
    }

    public function testConsumeTicketInsufficientHours()
    {
        $this->ticketService->addBalance($this->Customer, 5.0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('チケット残高が不足しています');

        $this->ticketService->consumeTicket(
            $this->Customer,
            10.0,
            'DXコンサル',
            'ヒアリング',
            new \DateTime('2026-03-01'),
            '佐藤花子'
        );
    }

    public function testGetUsageHistory()
    {
        $this->ticketService->addBalance($this->Customer, 50.0);

        $this->ticketService->consumeTicket(
            $this->Customer, 3.0, 'Web制作', '作業1',
            new \DateTime('2026-03-01'), 'スタッフA'
        );
        $this->ticketService->consumeTicket(
            $this->Customer, 2.0, 'デザイン', '作業2',
            new \DateTime('2026-03-02'), 'スタッフB'
        );

        $history = $this->ticketService->getUsageHistory($this->Customer);

        $this->assertCount(2, $history);
    }

    public function testConsumeTicketSkipsExpiredBalances()
    {
        // 期限切れ残高
        $expired = new TicketBalance();
        $expired->setCustomer($this->Customer);
        $expired->setTotalHours(100.0);
        $expired->setUsedHours(0);
        $expired->setExpiresAt(new \DateTime('-1 day'));
        $expired->setCreateDate(new \DateTime('-7 months'));
        $expired->setUpdateDate(new \DateTime('-7 months'));
        $this->ticketBalanceRepository->save($expired);

        // 有効な残高
        $this->ticketService->addBalance($this->Customer, 5.0);

        $this->ticketService->consumeTicket(
            $this->Customer, 3.0, 'Web制作', '作業',
            new \DateTime('2026-03-01'), 'スタッフ'
        );

        $this->entityManager->refresh($expired);
        $this->assertEquals(0, $expired->getUsedHours()); // 期限切れは消化されない

        $remaining = $this->ticketService->getRemainingHours($this->Customer);
        $this->assertEquals(2.0, $remaining);
    }
}
