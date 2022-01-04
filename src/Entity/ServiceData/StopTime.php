<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\StopTimeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StopTimeRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="time_trip_id", columns={"schema_trip_id"}),
 *     @ORM\Index(name="time_stop_id", columns={"schema_stop_id"}),
 * })
 */
class StopTime
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
    private $schemaTripId;

    /**
     * @ORM\ManyToOne(targetEntity=Trip::class, inversedBy="stopTimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trip;

    /**
     * @ORM\Column(type="time")
     */
    private $arrivalTime;

    /**
     * @ORM\Column(type="time")
     */
    private $departureTime;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $schemaStopId;

    /**
     * @ORM\ManyToOne(targetEntity=Stop::class, inversedBy="stopTimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $stop;

    /**
     * @ORM\Column(type="integer")
     */
    private $stopSequence;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): self
    {
        $this->trip = $trip;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTimeInterface $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTimeInterface $departureTime): self
    {
        $this->departureTime = $departureTime;

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

    public function getStop(): ?Stop
    {
        return $this->stop;
    }

    public function setStop(?Stop $stop): self
    {
        $this->stop = $stop;

        return $this;
    }

    public function getStopSequence(): ?int
    {
        return $this->stopSequence;
    }

    public function setStopSequence(int $stopSequence): self
    {
        $this->stopSequence = $stopSequence;

        return $this;
    }
}
