<?php

namespace App\Entity;

use App\Repository\NotificationLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationLogRepository::class)
 */
class NotificationLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateSend;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDelivered;

    /**
     * @ORM\Column(type="text")
     */
    private $deviceToken;

    /**
     * @ORM\Column(type="array")
     */
    private $message = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateSend(): ?\DateTimeInterface
    {
        return $this->dateSend;
    }

    public function setDateSend(\DateTimeInterface $dateSend): self
    {
        $this->dateSend = $dateSend;

        return $this;
    }

    public function getDateDelivered(): ?\DateTimeInterface
    {
        return $this->dateDelivered;
    }

    public function setDateDelivered(?\DateTimeInterface $dateDelivered): self
    {
        $this->dateDelivered = $dateDelivered;

        return $this;
    }

    public function getDeviceToken(): ?string
    {
        return $this->deviceToken;
    }

    public function setDeviceToken(string $deviceToken): self
    {
        $this->deviceToken = $deviceToken;

        return $this;
    }

    public function getMessage(): ?array
    {
        return $this->message;
    }

    public function setMessage(array $message): self
    {
        $this->message = $message;

        return $this;
    }
}
