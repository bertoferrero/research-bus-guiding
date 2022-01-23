<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\FrequenciesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FrequenciesRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="schema_trip_id", columns={"schema_trip_id"})
 * })
 */
class Frequencies
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
     * @ORM\Column(type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(type="time")
     */
    private $endTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $headwaySecs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchemaTripId(): ?string
    {
        return $this->schemaTripId;
    }

    public function setSchemaTripId(string $schemaTripId): self
    {
        $this->schemaTripId = $schemaTripId;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getHeadwaySecs(): ?int
    {
        return $this->headwaySecs;
    }

    public function setHeadwaySecs(int $headwaySecs): self
    {
        $this->headwaySecs = $headwaySecs;

        return $this;
    }
}
