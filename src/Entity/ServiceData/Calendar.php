<?php

namespace App\Entity\ServiceData;

use App\Repository\ServiceData\CalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalendarRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(name="schema_id", columns={"schema_id"} )
 * })
 */
class Calendar
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
     * @ORM\OneToOne(targetEntity=CalendarPlan::class, mappedBy="calendar", cascade={"persist", "remove"})
     */
    private $calendarPlan;

    /**
     * @ORM\OneToMany(targetEntity=CalendarDates::class, mappedBy="calendar", orphanRemoval=true)
     */
    private $calendarDates;

    /**
     * @ORM\OneToMany(targetEntity=Trip::class, mappedBy="calendar")
     */
    private $trips;

    public function __construct()
    {
        $this->calendarDates = new ArrayCollection();
        $this->trips = new ArrayCollection();
    }

    

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

    public function getCalendarPlan(): ?CalendarPlan
    {
        return $this->calendarPlan;
    }

    public function setCalendarPlan(CalendarPlan $calendarPlan): self
    {
        // set the owning side of the relation if necessary
        if ($calendarPlan->getCalendar() !== $this) {
            $calendarPlan->setCalendar($this);
        }

        $this->calendarPlan = $calendarPlan;

        return $this;
    }

    /**
     * @return Collection|CalendarDates[]
     */
    public function getCalendarDates(): Collection
    {
        return $this->calendarDates;
    }

    public function addCalendarDate(CalendarDates $calendarDate): self
    {
        if (!$this->calendarDates->contains($calendarDate)) {
            $this->calendarDates[] = $calendarDate;
            $calendarDate->setCalendar($this);
        }

        return $this;
    }

    public function removeCalendarDate(CalendarDates $calendarDate): self
    {
        if ($this->calendarDates->removeElement($calendarDate)) {
            // set the owning side to null (unless already changed)
            if ($calendarDate->getCalendar() === $this) {
                $calendarDate->setCalendar(null);
            }
        }

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
            $trip->setCalendar($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): self
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getCalendar() === $this) {
                $trip->setCalendar(null);
            }
        }

        return $this;
    }
}
