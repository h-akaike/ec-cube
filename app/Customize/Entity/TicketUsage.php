<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;

/**
 * チケット消化履歴エンティティ
 *
 * @ORM\Table(name="dtb_ticket_usage")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Customize\Repository\TicketUsageRepository")
 */
class TicketUsage extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Customize\Entity\TicketBalance", inversedBy="TicketUsages")
     * @ORM\JoinColumn(name="ticket_balance_id", referencedColumnName="id", nullable=false)
     *
     * @var TicketBalance
     */
    private $TicketBalance;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     *
     * @var Customer
     */
    private $Customer;

    /**
     * @ORM\Column(name="hours", type="decimal", precision=10, scale=1)
     *
     * @var string
     */
    private $hours;

    /**
     * @ORM\Column(name="service_type", type="string", length=50)
     *
     * @var string
     */
    private $service_type;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string|null
     */
    private $description;

    /**
     * @ORM\Column(name="work_date", type="date")
     *
     * @var \DateTimeInterface
     */
    private $work_date;

    /**
     * @ORM\Column(name="staff_name", type="string", length=100)
     *
     * @var string
     */
    private $staff_name;

    /**
     * @ORM\Column(name="create_date", type="datetimetz")
     *
     * @var \DateTimeInterface
     */
    private $create_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketBalance(): ?TicketBalance
    {
        return $this->TicketBalance;
    }

    public function setTicketBalance(TicketBalance $TicketBalance): self
    {
        $this->TicketBalance = $TicketBalance;

        return $this;
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

    public function getHours(): float
    {
        return (float) $this->hours;
    }

    public function setHours(float $hours): self
    {
        $this->hours = (string) $hours;

        return $this;
    }

    public function getServiceType(): ?string
    {
        return $this->service_type;
    }

    public function setServiceType(string $serviceType): self
    {
        $this->service_type = $serviceType;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getWorkDate(): ?\DateTimeInterface
    {
        return $this->work_date;
    }

    public function setWorkDate(\DateTimeInterface $workDate): self
    {
        $this->work_date = $workDate;

        return $this;
    }

    public function getStaffName(): ?string
    {
        return $this->staff_name;
    }

    public function setStaffName(string $staffName): self
    {
        $this->staff_name = $staffName;

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
}
