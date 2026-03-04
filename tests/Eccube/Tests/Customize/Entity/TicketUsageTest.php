<?php

namespace Eccube\Tests\Customize\Entity;

use Customize\Entity\TicketBalance;
use Customize\Entity\TicketUsage;
use Eccube\Tests\EccubeTestCase;

class TicketUsageTest extends EccubeTestCase
{
    public function testGettersAndSetters()
    {
        $usage = new TicketUsage();
        $Customer = $this->createCustomer();

        $result = $usage->setCustomer($Customer);
        $this->assertSame($usage, $result, 'setCustomer should return $this');
        $this->assertSame($Customer, $usage->getCustomer());

        $balance = new TicketBalance();
        $result = $usage->setTicketBalance($balance);
        $this->assertSame($usage, $result);
        $this->assertSame($balance, $usage->getTicketBalance());

        $result = $usage->setHours(2.5);
        $this->assertSame($usage, $result);
        $this->assertEquals(2.5, $usage->getHours());

        $result = $usage->setServiceType('Web制作');
        $this->assertSame($usage, $result);
        $this->assertEquals('Web制作', $usage->getServiceType());

        $result = $usage->setDescription('トップページのコーディング');
        $this->assertSame($usage, $result);
        $this->assertEquals('トップページのコーディング', $usage->getDescription());

        $workDate = new \DateTime('2026-03-01');
        $result = $usage->setWorkDate($workDate);
        $this->assertSame($usage, $result);
        $this->assertSame($workDate, $usage->getWorkDate());

        $result = $usage->setStaffName('田中太郎');
        $this->assertSame($usage, $result);
        $this->assertEquals('田中太郎', $usage->getStaffName());
    }

    public function testCreateDate()
    {
        $usage = new TicketUsage();
        $now = new \DateTime();
        $usage->setCreateDate($now);

        $this->assertSame($now, $usage->getCreateDate());
    }
}
