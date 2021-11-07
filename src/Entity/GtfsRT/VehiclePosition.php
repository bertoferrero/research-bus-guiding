<?php

namespace App\Entity\GtfsRT;

use App\Repository\GtfsRT\VehiclePositionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Lib\Enum\VehiclePositionStatusEnum;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass=VehiclePositionRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="vehicle_trip_id", columns={"gtfs_trip_id"}),
 *     @ORM\Index(name="vehicle_stop_id", columns={"gtfs_stop_id"}),
 * },
 * uniqueConstraints={
 *  @ORM\UniqueConstraint(name="vehicle_id", columns={"gtfs_vehicle_id"} )
 * })
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
     * @ORM\Column(type="string", length=255)
     */
    private $gtfsVehicleId;

    /**
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gtfsTripId;

    /**
     * @ORM\Column(type="integer")
     */
    private $gtfsStopId;

    /**
     * @ORM\Column(type="VehiclePositionStatusEnum")
     * DoctrineAssert\Enum(entity="App\Lib\Enum\VehiclePositionStatusEnum")   
     */
    private $currentStatus = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGtfsVehicleId(): ?string
    {
        return $this->gtfsVehicleId;
    }

    public function setGtfsVehicleId(string $gtfsVehicleId): self
    {
        $this->gtfsVehicleId = $gtfsVehicleId;

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

    public function getGtfsTripId(): ?string
    {
        return $this->gtfsTripId;
    }

    public function setGtfsTripId(string $gtfsTripId): self
    {
        $this->gtfsTripId = $gtfsTripId;

        return $this;
    }

    public function getGtfsStopId(): ?int
    {
        return $this->gtfsStopId;
    }

    public function setGtfsStopId(int $gtfsStopId): self
    {
        $this->gtfsStopId = $gtfsStopId;

        return $this;
    }

    public function getCurrentStatus(): ?string
    {
        return $this->currentStatus;
    }

    public function setCurrentStatus(string $currentStatus): self
    {
        $this->currentStatus = $currentStatus;

        return $this;
    }
}
