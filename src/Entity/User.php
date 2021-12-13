<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
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
     * @ORM\Column(type="string", length=256, nullable=true, unique=true)
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
}
