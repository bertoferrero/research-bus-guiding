<?php

namespace App\Entity;

use App\Entity\ServiceData\Route;
use App\Entity\ServiceData\VehiclePosition;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *  @ORM\Table(indexes={
 *     @ORM\Index(name="driver_vehicle_id", columns={"driver_vehicle_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=128, nullable=true, unique=true)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity=UserNotificationTopicSubscription::class, mappedBy="user", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $notificationTopicSubscriptions;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notificationDeviceToken;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $driverVehicleId;

    /**
     * @ORM\OneToOne(targetEntity=VehiclePosition::class, mappedBy="driver", cascade={"persist", "remove"})
     */
    private $vehiclePosition;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="drivers")
     */
    private $driverRoute;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $driverLatitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $driverLongitude;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;

    public function __construct()
    {
        $this->notificationTopicSubscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection|UserNotificationTopicSubscription[]
     */
    public function getNotificationTopicSubscriptions(): Collection
    {
        return $this->notificationTopicSubscriptions;
    }

    public function addNotificationTopicSubscription(UserNotificationTopicSubscription $notificationTopicSubscription): self
    {
        if (!$this->notificationTopicSubscriptions->contains($notificationTopicSubscription)) {
            $this->notificationTopicSubscriptions[] = $notificationTopicSubscription;
            $notificationTopicSubscription->setUser($this);
        }

        return $this;
    }

    public function removeNotificationTopicSubscription(UserNotificationTopicSubscription $notificationTopicSubscription): self
    {
        if ($this->notificationTopicSubscriptions->removeElement($notificationTopicSubscription)) {
            // set the owning side to null (unless already changed)
            if ($notificationTopicSubscription->getUser() === $this) {
                $notificationTopicSubscription->setUser(null);
            }
        }

        return $this;
    }

    public function getNotificationDeviceToken(): ?string
    {
        return $this->notificationDeviceToken;
    }

    public function setNotificationDeviceToken(?string $notificationDeviceToken): self
    {
        $this->notificationDeviceToken = $notificationDeviceToken;

        return $this;
    }

    public function getDriverVehicleId(): ?string
    {
        return $this->driverVehicleId;
    }

    public function setDriverVehicleId(?string $driverVehicleId): self
    {
        $this->driverVehicleId = $driverVehicleId;

        return $this;
    }

    public function getVehiclePosition(): ?VehiclePosition
    {
        return $this->vehiclePosition;
    }

    public function setVehiclePosition(?VehiclePosition $vehiclePosition): self
    {
        // unset the owning side of the relation if necessary
        if ($this->vehiclePosition !== null && $vehiclePosition?->getId() != $this->vehiclePosition->getId()) {
            $this->vehiclePosition->setDriver(null);
        }

        // set the owning side of the relation if necessary
        if ($vehiclePosition !== null && $vehiclePosition->getDriver() !== $this) {
            $vehiclePosition->setDriver($this);
        }

        $this->vehiclePosition = $vehiclePosition;

        return $this;
    }

    public function getDriverRoute(): ?Route
    {
        return $this->driverRoute;
    }

    public function setDriverRoute(?Route $driverRoute): self
    {
        $this->driverRoute = $driverRoute;

        return $this;
    }

    public function getDriverLatitude(): ?float
    {
        return $this->driverLatitude;
    }

    public function setDriverLatitude(?float $driverLatitude): self
    {
        $this->driverLatitude = $driverLatitude;

        return $this;
    }

    public function getDriverLongitude(): ?float
    {
        return $this->driverLongitude;
    }

    public function setDriverLongitude(?float $driverLongitude): self
    {
        $this->driverLongitude = $driverLongitude;

        return $this;
    }

    public function getDateUpd(): ?\DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTimeInterface $dateUpd): self
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setDateUpdValue(): void
    {
        $this->dateUpd = new \DateTimeImmutable();
    }
}
