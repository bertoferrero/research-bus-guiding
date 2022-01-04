<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TripRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="trip_schema_id", columns={"schema_id"} )
 * })
 */
class Trip
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
    private $schemaRouteId;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $route;

    /**
     * @ORM\OneToMany(targetEntity=StopTime::class, mappedBy="trip")
     */
    private $stopTimes;

    public function __construct()
    {
        $this->stopTimes = new ArrayCollection();
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

    public function getschemaRouteId(): ?string
    {
        return $this->schemaRouteId;
    }

    public function setschemaRouteId(string $schemaRouteId): self
    {
        $this->schemaRouteId = $schemaRouteId;

        return $this;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setRoute(?Route $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return Collection|StopTime[]
     */
    public function getStopTimes(): Collection
    {
        return $this->stopTimes;
    }

    public function addStopTime(StopTime $stopTime): self
    {
        if (!$this->stopTimes->contains($stopTime)) {
            $this->stopTimes[] = $stopTime;
            $stopTime->setTrip($this);
        }

        return $this;
    }

    public function removeStopTime(StopTime $stopTime): self
    {
        if ($this->stopTimes->removeElement($stopTime)) {
            // set the owning side to null (unless already changed)
            if ($stopTime->getTrip() === $this) {
                $stopTime->setTrip(null);
            }
        }

        return $this;
    }
}
