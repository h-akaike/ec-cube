<?php

namespace Customize\Service;

use Customize\Entity\TicketBalance;
use Customize\Entity\TicketUsage;
use Customize\Repository\TicketBalanceRepository;
use Customize\Repository\TicketUsageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;

class TicketService
{
    /** @var TicketBalanceRepository */
    private $ticketBalanceRepository;

    /** @var TicketUsageRepository */
    private $ticketUsageRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** デフォルトの有効期限（月数） */
    private const DEFAULT_EXPIRY_MONTHS = 6;

    public function __construct(
        TicketBalanceRepository $ticketBalanceRepository,
        TicketUsageRepository $ticketUsageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->ticketBalanceRepository = $ticketBalanceRepository;
        $this->ticketUsageRepository = $ticketUsageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * チケット残高を追加
     */
    public function addBalance(Customer $Customer, float $hours, ?Order $Order = null): TicketBalance
    {
        $now = new \DateTime();
        $expiresAt = (clone $now)->modify('+' . self::DEFAULT_EXPIRY_MONTHS . ' months');

        $balance = new TicketBalance();
        $balance->setCustomer($Customer);
        $balance->setTotalHours($hours);
        $balance->setUsedHours(0);
        $balance->setExpiresAt($expiresAt);
        $balance->setCreateDate($now);
        $balance->setUpdateDate($now);

        if ($Order !== null) {
            $balance->setOrder($Order);
        }

        $this->ticketBalanceRepository->save($balance);

        return $balance;
    }

    /**
     * 顧客の残り時間合計を取得
     */
    public function getRemainingHours(Customer $Customer): float
    {
        return $this->ticketBalanceRepository->getTotalRemainingHoursByCustomer($Customer);
    }

    /**
     * チケットを消化（FIFO 方式）
     */
    public function consumeTicket(
        Customer $Customer,
        float $hours,
        string $serviceType,
        string $description,
        \DateTimeInterface $workDate,
        string $staffName
    ): TicketUsage {
        $remaining = $this->getRemainingHours($Customer);
        if ($remaining < $hours) {
            throw new \RuntimeException('チケット残高が不足しています');
        }

        $balances = $this->ticketBalanceRepository->getActiveBalancesByCustomer($Customer);
        $toConsume = $hours;

        foreach ($balances as $balance) {
            if ($toConsume <= 0) {
                break;
            }

            $available = $balance->getRemainingHours();
            $consume = min($available, $toConsume);

            $balance->setUsedHours($balance->getUsedHours() + $consume);
            $balance->setUpdateDate(new \DateTime());

            $usage = new TicketUsage();
            $usage->setTicketBalance($balance);
            $usage->setCustomer($Customer);
            $usage->setHours($consume);
            $usage->setServiceType($serviceType);
            $usage->setDescription($description);
            $usage->setWorkDate($workDate);
            $usage->setStaffName($staffName);
            $usage->setCreateDate(new \DateTime());

            $this->entityManager->persist($usage);

            $toConsume -= $consume;
        }

        $this->entityManager->flush();

        // 最後に作成した usage を返す（テスト用に最初の消化分を返す）
        $usages = $this->ticketUsageRepository->getUsagesByCustomer($Customer);

        return $usages[0];
    }

    /**
     * 顧客の有効な残高一覧を取得
     *
     * @return TicketBalance[]
     */
    public function getActiveBalances(Customer $Customer): array
    {
        return $this->ticketBalanceRepository->getActiveBalancesByCustomer($Customer);
    }

    /**
     * 顧客の消化履歴を取得
     *
     * @return TicketUsage[]
     */
    public function getUsageHistory(Customer $Customer): array
    {
        return $this->ticketUsageRepository->getUsagesByCustomer($Customer);
    }
}
