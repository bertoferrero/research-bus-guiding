<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\ShapeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShapePointRepository::class)
 */
class ShapePoint
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
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\OneToOne(targetEntity=ShapePoint::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $nextPoint;

    /**
     * @ORM\OneToOne(targetEntity=ShapePoint::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $prevPoint;

    /**
     * @ORM\ManyToOne(targetEntity=Stop::class)
     */
    private $stop;

    /**
     * @ORM\ManyToOne(targetEntity=Stop::class)
     */
    private $nextStopInRoute;

    /**
     * @ORM\Column(type="float")
     */
    private $nextStopRemainingDistance = 0;

    /**
     * @ORM\ManyToOne(targetEntity=Shape::class, inversedBy="shapePoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shape;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNextPoint(): ?self
    {
        return $this->nextPoint;
    }

    public function setNextPoint(?self $nextPoint): self
    {
        $this->nextPoint = $nextPoint;

        return $this;
    }

    public function getPrevPoint(): ?self
    {
        return $this->prevPoint;
    }

    public function setPrevPoint(?self $prevPoint): self
    {
        $this->prevPoint = $prevPoint;

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

    public function getNextStopInRoute(): ?Stop
    {
        return $this->nextStopInRoute;
    }

    public function setNextStopInRoute(?Stop $nextStopInRoute): self
    {
        $this->nextStopInRoute = $nextStopInRoute;

        return $this;
    }

    public function getNextStopRemainingDistance(): ?float
    {
        return $this->nextStopRemainingDistance;
    }

    public function setNextStopRemainingDistance(float $nextStopRemainingDistance): self
    {
        $this->nextStopRemainingDistance = $nextStopRemainingDistance;

        return $this;
    }

    public function getShape(): ?Shape
    {
        return $this->shape;
    }

    public function setShape(?Shape $shape): self
    {
        $this->shape = $shape;

        return $this;
    }
}
