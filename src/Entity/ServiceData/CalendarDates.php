<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\CalendarDatesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalendarDatesRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="schema_date_id", columns={"schema_id", "date"} )
 * })
 */
class CalendarDates
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
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRemovingDate;

    /**
     * @ORM\ManyToOne(targetEntity=Calendar::class, inversedBy="calendarDates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $calendar;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIsRemovingDate(): ?bool
    {
        return $this->isRemovingDate;
    }

    public function setIsRemovingDate(bool $isRemovingDate): self
    {
        $this->isRemovingDate = $isRemovingDate;

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
}
