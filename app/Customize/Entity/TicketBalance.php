<?php

namespace Customize\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;

/**
 * チケット残高エンティティ
 *
 * @ORM\Table(name="dtb_ticket_balance")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Customize\Repository\TicketBalanceRepository")
 */
class TicketBalance extends AbstractEntity
{
    /**
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     *
     * @var Customer
     */
    private $Customer;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     *
     * @var Order|null
     */
    private $Order;

    /**
     * @ORM\Column(name="total_hours", type="decimal", precision=10, scale=1)
     *
     * @var string
     */
    private $total_hours;

    /**
     * @ORM\Column(name="used_hours", type="decimal", precision=10, scale=1, options={"default":0})
     *
     * @var string
     */
    private $used_hours = '0';

    /**
     * @ORM\Column(name="expires_at", type="datetimetz")
     *
     * @var \DateTimeInterface
     */
    private $expires_at;

    /**
     * @ORM\Column(name="create_date", type="datetimetz")
     *
     * @var \DateTimeInterface
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetimetz")
     *
     * @var \DateTimeInterface
     */
    private $update_date;

    /**
     * @ORM\OneToMany(targetEntity="Customize\Entity\TicketUsage", mappedBy="TicketBalance")
     *
     * @var Collection
     */
    private $TicketUsages;

    public function __construct()
    {
        $this->TicketUsages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->Customer;
    }

    public function setCustomer(Customer $Customer): self
    {
        $this->Customer = $Customer;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->Order;
    }

    public function setOrder(?Order $Order): self
    {
        $this->Order = $Order;

        return $this;
    }

    public function getTotalHours(): float
    {
        return (float) $this->total_hours;
    }

    public function setTotalHours(float $totalHours): self
    {
        $this->total_hours = (string) $totalHours;

        return $this;
    }

    public function getUsedHours(): float
    {
        return (float) $this->used_hours;
    }

    public function setUsedHours(float $usedHours): self
    {
        $this->used_hours = (string) $usedHours;

        return $this;
    }

    public function getRemainingHours(): float
    {
        return $this->getTotalHours() - $this->getUsedHours();
    }

    public function isExpired(): bool
    {
        return $this->expires_at < new \DateTime();
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expires_at;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expires_at = $expiresAt;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->create_date;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }

    public function getTicketUsages(): Collection
    {
        return $this->TicketUsages;
    }
}
