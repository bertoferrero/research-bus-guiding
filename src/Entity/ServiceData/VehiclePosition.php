<?php

namespace App\Entity\ServiceData;

use App\Entity\User;
use App\Repository\ServiceData\VehiclePositionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=VehiclePositionRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="vehicle_trip_id", columns={"schema_trip_id"}),
 *     @ORM\Index(name="vehicle_stop_id", columns={"schema_stop_id"}),
 * },
 * uniqueConstraints={
 *  @ORM\UniqueConstraint(name="vehicle_id", columns={"schema_vehicle_id"} )
 * })
 * @Gedmo\Loggable
 */
class VehiclePosition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Versioned
     */
    private $schemaVehicleId;

    /**
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Versioned
     */
    private $schemaTripId;

    /**
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Versioned
     */
    private $schemaStopId;

    /**
     * @ORM\Column(type="VehiclePositionStatusEnum", nullable=true)
     * DoctrineAssert\Enum(entity="App\Lib\Enum\VehiclePositionStatusEnum")   
     * @Gedmo\Versioned
     */
    private $currentStatus;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="vehiclePosition", cascade={"persist", "remove"})
     */
    private $driver;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getschemaVehicleId(): ?string
    {
        return $this->schemaVehicleId;
    }

    public function setschemaVehicleId(string $schemaVehicleId): self
    {
        $this->schemaVehicleId = $schemaVehicleId;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getschemaTripId(): ?string
    {
        return $this->schemaTripId;
    }

    public function setschemaTripId(string $schemaTripId): self
    {
        $this->schemaTripId = $schemaTripId;

        return $this;
    }

    public function getschemaStopId(): ?string
    {
        return $this->schemaStopId;
    }

    public function setschemaStopId(string $schemaStopId): self
    {
        $this->schemaStopId = $schemaStopId;

        return $this;
    }

    public function getCurrentStatus(): ?string
    {
        return $this->currentStatus;
    }

    public function setCurrentStatus(?string $currentStatus): self
    {
        $this->currentStatus = $currentStatus;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }
}
