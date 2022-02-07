<?php

namespace App\Entity\ServiceData;

use App\Entity\User;
use App\Repository\ServiceData\RouteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="route_schema_id", columns={"schema_id"} )
 * })
 */
class Route
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $schemaId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity=Trip::class, mappedBy="route", cascade={"remove"})
     */
    private $trips;

    /**
     * @ORM\OneToMany(targetEntity=VehiclePosition::class, mappedBy="route")
     */
    private $vehiclePositions;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="driverRoute")
     */
    private $drivers;

    public function __toString()
    {
        return $this->schemaId;
    }

    public function __construct()
    {
        $this->trips = new ArrayCollection();
        $this->vehiclePositions = new ArrayCollection();
        $this->drivers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getschemaId(): ?string
    {
        return $this->schemaId;
    }

    public function setschemaId(string $schemaId): self
    {
        $this->schemaId = $schemaId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Trip[]
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }

    public function addTrip(Trip $trip): self
    {
        if (!$this->trips->contains($trip)) {
            $this->trips[] = $trip;
            $trip->setRoute($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): self
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getRoute() === $this) {
                $trip->setRoute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VehiclePosition[]
     */
    public function getVehiclePositions(): Collection
    {
        return $this->vehiclePositions;
    }

    public function addVehiclePosition(VehiclePosition $vehiclePosition): self
    {
        if (!$this->vehiclePositions->contains($vehiclePosition)) {
            $this->vehiclePositions[] = $vehiclePosition;
            $vehiclePosition->setRoute($this);
        }

        return $this;
    }

    public function removeVehiclePosition(VehiclePosition $vehiclePosition): self
    {
        if ($this->vehiclePositions->removeElement($vehiclePosition)) {
            // set the owning side to null (unless already changed)
            if ($vehiclePosition->getRoute() === $this) {
                $vehiclePosition->setRoute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(User $driver): self
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers[] = $driver;
            $driver->setDriverRoute($this);
        }

        return $this;
    }

    public function removeDriver(User $driver): self
    {
        if ($this->drivers->removeElement($driver)) {
            // set the owning side to null (unless already changed)
            if ($driver->getDriverRoute() === $this) {
                $driver->setDriverRoute(null);
            }
        }

        return $this;
    }
}
