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
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="trips", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $route;

    /**
     * @ORM\OneToMany(targetEntity=StopTime::class, mappedBy="trip")
     */
    private $stopTimes;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $schemaShapeId;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $schemaServiceId;

    /**
     * @ORM\ManyToOne(targetEntity=Calendar::class, inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $calendar;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $hourStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $hourEnd;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $md5StopSequence;

    public function __construct()
    {
        $this->stopTimes = new ArrayCollection();
        $this->shapes = new ArrayCollection();
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

    public function getSchemaShapeId(): ?string
    {
        return $this->schemaShapeId;
    }

    public function setSchemaShapeId(?string $schemaShapeId): self
    {
        $this->schemaShapeId = $schemaShapeId;

        return $this;
    }

    public function getSchemaServiceId(): ?string
    {
        return $this->schemaServiceId;
    }

    public function setSchemaServiceId(string $schemaServiceId): self
    {
        $this->schemaServiceId = $schemaServiceId;

        return $this;
    }

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): self
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getHourStart(): ?\DateTimeInterface
    {
        return $this->hourStart;
    }

    public function setHourStart(?\DateTimeInterface $hourStart): self
    {
        $this->hourStart = $hourStart;

        return $this;
    }

    public function getHourEnd(): ?\DateTimeInterface
    {
        return $this->hourEnd;
    }

    public function setHourEnd(?\DateTimeInterface $hourEnd): self
    {
        $this->hourEnd = $hourEnd;

        return $this;
    }

    public function getMd5StopSequence(): ?string
    {
        return $this->md5StopSequence;
    }

    public function setMd5StopSequence(?string $md5StopSequence): self
    {
        $this->md5StopSequence = $md5StopSequence;

        return $this;
    }
}
