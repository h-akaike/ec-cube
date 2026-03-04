<?php

namespace Customize\Repository;

use Customize\Entity\TicketUsage;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;

class TicketUsageRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketUsage::class);
    }

    /**
     * @param TicketUsage $entity
     */
    public function save($entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * 顧客の消化履歴を新しい順に取得
     *
     * @return TicketUsage[]
     */
    public function getUsagesByCustomer(Customer $Customer): array
    {
        return $this->findBy(
            ['Customer' => $Customer],
            ['create_date' => 'DESC']
        );
    }
}
