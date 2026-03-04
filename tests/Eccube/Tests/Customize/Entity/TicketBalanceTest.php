<?php

namespace Eccube\Tests\Customize\Entity;

use Customize\Entity\TicketBalance;
use Eccube\Tests\EccubeTestCase;

class TicketBalanceTest extends EccubeTestCase
{
    public function testGettersAndSetters()
    {
        $balance = new TicketBalance();
        $Customer = $this->createCustomer();

        $result = $balance->setCustomer($Customer);
        $this->assertSame($balance, $result, 'setCustomer should return $this');
        $this->assertSame($Customer, $balance->getCustomer());

        $result = $balance->setTotalHours(20.0);
        $this->assertSame($balance, $result);
        $this->assertEquals(20.0, $balance->getTotalHours());

        $result = $balance->setUsedHours(5.0);
        $this->assertSame($balance, $result);
        $this->assertEquals(5.0, $balance->getUsedHours());

        $expires = new \DateTime('+6 months');
        $result = $balance->setExpiresAt($expires);
        $this->assertSame($balance, $result);
        $this->assertSame($expires, $balance->getExpiresAt());
    }

    public function testGetRemainingHours()
    {
        $balance = new TicketBalance();
        $balance->setTotalHours(20.0);
        $balance->setUsedHours(7.5);

        $this->assertEquals(12.5, $balance->getRemainingHours());
    }

    public function testGetRemainingHoursDefault()
    {
        $balance = new TicketBalance();
        $balance->setTotalHours(10.0);

        $this->assertEquals(10.0, $balance->getRemainingHours());
    }

    public function testIsExpiredWhenNotExpired()
    {
        $balance = new TicketBalance();
        $balance->setExpiresAt(new \DateTime('+1 month'));

        $this->assertFalse($balance->isExpired());
    }

    public function testIsExpiredWhenExpired()
    {
        $balance = new TicketBalance();
        $balance->setExpiresAt(new \DateTime('-1 day'));

        $this->assertTrue($balance->isExpired());
    }

    public function testDefaultUsedHoursIsZero()
    {
        $balance = new TicketBalance();
        $this->assertEquals(0, $balance->getUsedHours());
    }

    public function testTimestamps()
    {
        $balance = new TicketBalance();
        $now = new \DateTime();

        $balance->setCreateDate($now);
        $balance->setUpdateDate($now);

        $this->assertSame($now, $balance->getCreateDate());
        $this->assertSame($now, $balance->getUpdateDate());
    }
}
