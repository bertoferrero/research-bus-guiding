<?php

namespace App\Entity;

use App\Repository\StopRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Lib\Enum\StopRequestStatusEnum;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=StopRequestRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="status_idx", columns={"status"}),
 *     @ORM\Index(name="vehicle_id_idx", columns={"schema_vehicle_id"}),
 *     @ORM\Index(name="line_id_idx", columns={"schema_line_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class StopRequest
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $schemaVehicleId;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $schemaLineId;

    /**
     * @ORM\Column(type="integer")
     */
    private $schemaStopId;

    /**
     * @ORM\Column(type="StopRequestStatusEnum", nullable=true)
     * DoctrineAssert\Enum(entity="App\Lib\Enum\StopRequestStatusEnum")   
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;

    public function __construct()
    {
        $this->status = StopRequestStatusEnum::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchemaVehicleId(): ?string
    {
        return $this->schemaVehicleId;
    }

    public function setSchemaVehicleId(?string $schemaVehicleId): self
    {
        $this->schemaVehicleId = $schemaVehicleId;

        return $this;
    }

    public function getSchemaLineId(): ?string
    {
        return $this->schemaLineId;
    }

    public function setSchemaLineId(?string $schemaLineId): self
    {
        $this->schemaLineId = $schemaLineId;

        return $this;
    }

    public function getSchemaStopId(): ?int
    {
        return $this->schemaStopId;
    }

    public function setSchemaStopId(int $schemaStopId): self
    {
        $this->schemaStopId = $schemaStopId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpd(): ?\DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTimeInterface $dateUpd): self
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationValue(): void
    {
        $this->dateAdd = new \DateTimeImmutable();
        $this->dateUpd = new \DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateValue(): void
    {
        $this->dateUpd = new \DateTimeImmutable();
    }
}
