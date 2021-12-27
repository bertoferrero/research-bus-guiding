<?php

namespace App\Entity;

use App\Repository\SampleLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SampleLogRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class SampleLog
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
    private $type;

    /**
     * @ORM\Column(type="datetime", options={"comment":"Datetime about the sample event, e.g. When the vehicle arrives at the stop, when the mobile phone receives the notification..."})
     */
    private $sampleDateTime;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"Only if it is required, e.g. When the notification was send"})
     */
    private $sampleDateSend;

    /**
     * @ORM\Column(type="datetime", options={"comment":"Just for knowing if the mobile phone's clock is synchronized. It contains the moment when the row has been inserted"})
     */
    private $sampleServerDateTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSampleDateTime(): ?\DateTimeInterface
    {
        return $this->sampleDateTime;
    }

    public function setSampleDateTime(\DateTimeInterface $sampleDateTime): self
    {
        $this->sampleDateTime = $sampleDateTime;

        return $this;
    }

    public function getSampleDateSend(): ?\DateTimeInterface
    {
        return $this->sampleDateSend;
    }

    public function setSampleDateSend(?\DateTimeInterface $sampleDateSend): self
    {
        $this->sampleDateSend = $sampleDateSend;

        return $this;
    }

    public function getSampleServerDateTime(): ?\DateTimeInterface
    {
        return $this->sampleServerDateTime;
    }

    public function setSampleServerDateTime(\DateTimeInterface $sampleServerDateTime): self
    {
        $this->sampleServerDateTime = $sampleServerDateTime;

        return $this;
    }

    
    /**
     * @ORM\PrePersist
     */
    public function setCreationValue(): void
    {
        $this->sampleServerDateTime = new \DateTimeImmutable();
    }
}
