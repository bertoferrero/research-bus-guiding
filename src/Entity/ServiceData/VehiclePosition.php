<?php

namespace App\Entity\ServiceData;

use App\Entity\User;
use App\Entity\ServiceData\Stop;
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
 * @ORM\HasLifecycleCallbacks()
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
     * 
     */
    private $schemaVehicleId;

    /**
     * @ORM\Column(type="float")
     * 
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     * 
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     */
    private $schemaTripId;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     */
    private $schemaRouteId;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * 
     */
    private $schemaStopId;

    /**
     * @ORM\Column(type="VehiclePositionStatusEnum", nullable=true)
     * DoctrineAssert\Enum(entity="App\Lib\Enum\VehiclePositionStatusEnum")   
     * 
     */
    private $currentStatus;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="vehiclePosition", cascade={"persist", "remove"})
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Trip::class)
     */
    private $trip;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class)
     */
    private $route;

    /**
     * @ORM\ManyToOne(targetEntity=ShapePoint::class)
     */
    private $lastShapePoint;

    /**
     * @ORM\Column(type="integer")
     */
    private $locationTries = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;

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

    protected function setschemaTripId(?string $schemaTripId): self
    {
        $this->schemaTripId = $schemaTripId;

        return $this;
    }

    public function getschemaStopId(): ?string
    {
        return $this->schemaStopId;
    }

    protected function setschemaStopId(?string $schemaStopId): self
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

    public function getSchemaRouteId(): ?string
    {
        return $this->schemaRouteId;
    }

    protected function setSchemaRouteId(?string $schemaRouteId): self
    {
        $this->schemaRouteId = $schemaRouteId;

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
     * @ORM\PreUpdate
     */
    public function setDateUpdValue(): void
    {
        $this->dateUpd = new \DateTimeImmutable();
    }

    public function getTrip(): ?trip
    {
        return $this->trip;
    }

    public function setTrip(?trip $trip): self
    {
        $this->trip = $trip;

        $this->route = $this->trip?->getRoute();
        $this->setSchemaRouteId($this->route?->getschemaId());
        $this->setschemaTripId($this->trip?->getschemaId());

        return $this;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setRoute(?Route $route): self
    {
        $this->setTrip(null);
        $this->route = $route;
        $this->setSchemaRouteId($this->route?->getschemaId());
        

        return $this;
    }

    public function getLastShapePoint(): ?ShapePoint
    {
        return $this->lastShapePoint;
    }

    public function setLastShapePoint(?ShapePoint $lastShapePoint): self
    {
        $this->lastShapePoint = $lastShapePoint;

        $this->setschemaStopId($lastShapePoint?->getNextStopInRoute()?->getschemaId());

        return $this;
    }

    public function getLocationTries(): ?int
    {
        return $this->locationTries;
    }

    public function setLocationTries(int $locationTries): self
    {
        $this->locationTries = $locationTries;

        return $this;
    }
}
