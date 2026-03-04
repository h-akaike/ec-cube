<?php

namespace Customize\Repository;

use Customize\Entity\TicketBalance;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;

class TicketBalanceRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketBalance::class);
    }

    /**
     * @param TicketBalance $entity
     */
    public function save($entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param TicketBalance $entity
     */
    public function delete($entity)
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * 有効な残高（期限内 & 残りあり）を作成日の古い順に取得
     *
     * @return TicketBalance[]
     */
    public function getActiveBalancesByCustomer(Customer $Customer): array
    {
        $qb = $this->createQueryBuilder('tb');
        $qb->where('tb.Customer = :customer')
            ->andWhere('tb.expires_at > :now')
            ->andWhere('tb.total_hours > tb.used_hours')
            ->setParameter('customer', $Customer)
            ->setParameter('now', new \DateTime())
            ->orderBy('tb.expires_at', 'ASC')
            ->addOrderBy('tb.create_date', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 顧客の有効な残り時間の合計を取得
     */
    public function getTotalRemainingHoursByCustomer(Customer $Customer): float
    {
        $balances = $this->getActiveBalancesByCustomer($Customer);
        $total = 0.0;
        foreach ($balances as $balance) {
            $total += $balance->getRemainingHours();
        }

        return $total;
    }
}
