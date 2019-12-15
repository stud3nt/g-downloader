<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Manager\UserManager;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Base\Entity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends Entity implements \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", nullable=false)
     * @Assert\Length(min="6")
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=false, length=64)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", nullable=false)
     */
    protected $username;

    /**
     * @var bool
     *
     * @ORM\Column(name="role", type="string")
     */
    protected $role = UserRole::Admin;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getUsername() ?: 'label.new_user';
    }

    public function enable()
    {
        $this->isActive = true;
    }

    public function isAdmin()
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /** @inheritDoc */
    public function getSalt()
    {
        return sprintf(sprintf('%s-%d', UserManager::Secret, $this->getId()));
    }

    /** @inheritDoc */
    public function getRoles()
    {
        $roles = [sprintf('ROLE_%s', strtoupper($this->getRole()))];

        return $roles;
    }

    /** @inheritDoc */
    public function eraseCredentials()
    {

    }

    public function serialize()
    {
        return serialize(array($this->id, $this->email, $this->password, $this->isActive));
    }

    public function unserialize($serialized)
    {
        list($this->id, $this->email, $this->password, $this->isActive) = unserialize($serialized);
    }

    public function isAccountNonExpired(): bool
    {
        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return true;
    }

    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->isActive;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
