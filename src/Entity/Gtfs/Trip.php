<?php

namespace App\Entity\Gtfs;

use App\Repository\Gtfs\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TripRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="trip_gtfs_id", columns={"gtfs_id"} )
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
     * @ORM\Column(type="string", length=255)
     */
    private $gtfsId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gtfsRouteId;

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

    public function getGtfsId(): ?string
    {
        return $this->gtfsId;
    }

    public function setGtfsId(string $gtfsId): self
    {
        $this->gtfsId = $gtfsId;

        return $this;
    }

    public function getGtfsRouteId(): ?string
    {
        return $this->gtfsRouteId;
    }

    public function setGtfsRouteId(string $gtfsRouteId): self
    {
        $this->gtfsRouteId = $gtfsRouteId;

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
