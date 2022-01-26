<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\ShapeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShapeRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="md5StopSequence_idx", columns={"md5_stop_sequence"} )
 * })
 */
class Shape
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $md5StopSequence;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $schemaId;

    /**
     * @ORM\OneToMany(targetEntity=Trip::class, mappedBy="shape")
     */
    private $trips;

    /**
     * @ORM\OneToMany(targetEntity=ShapePoint::class, mappedBy="shape", orphanRemoval=true)
     */
    private $shapePoints;

    public function __construct()
    {
        $this->trips = new ArrayCollection();
        $this->shapePoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMd5StopSequence(): ?string
    {
        return $this->md5StopSequence;
    }

    public function setMd5StopSequence(string $md5StopSequence): self
    {
        $this->md5StopSequence = $md5StopSequence;

        return $this;
    }

    public function getSchemaId(): ?string
    {
        return $this->schemaId;
    }

    public function setSchemaId(string $schemaId): self
    {
        $this->schemaId = $schemaId;

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
            $trip->setShape($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): self
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getShape() === $this) {
                $trip->setShape(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ShapePoint[]
     */
    public function getShapePoints(): Collection
    {
        return $this->shapePoints;
    }

    public function addShapePoint(ShapePoint $shapePoint): self
    {
        if (!$this->shapePoints->contains($shapePoint)) {
            $this->shapePoints[] = $shapePoint;
            $shapePoint->setShape($this);
        }

        return $this;
    }

    public function removeShapePoint(ShapePoint $shapePoint): self
    {
        if ($this->shapePoints->removeElement($shapePoint)) {
            // set the owning side to null (unless already changed)
            if ($shapePoint->getShape() === $this) {
                $shapePoint->setShape(null);
            }
        }

        return $this;
    }
}
