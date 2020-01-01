<?php

namespace App\Entity;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Enum\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends AbstractEntity implements UserInterface, \Serializable
{
    use CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
     * @EntityVariable(writable=true, readable=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=40, nullable=false, unique=true)
     * @EntityVariable(writable=true, readable=true)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=40, nullable=false, unique=true)
     * @EntityVariable(writable=true, readable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=60, nullable=false, unique=true)
     * @EntityVariable(writable=true, readable=true)
     */
    protected $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     * @Assert\Length(min="6")
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=false, length=64)
     * @EntityVariable(writable=false, readable=true)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", nullable=false, length=64)
     */
    protected $salt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array", nullable=false)
     */
    protected $roles = [UserRole::Admin];

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_logged_at", type="datetime", nullable=true)
     */
    protected $lastLoggedAt;

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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getSalt()
    {
        return sprintf(sprintf('%s-%d', $this->salt, $this->getId()));
    }

    public function eraseCredentials(): self
    {
        $this->roles = [];

        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id, $this->username, $this->password, $this->salt,
        ));
    }

    public function unserialize($serialized)
    {
        list ($this->id, $this->username, $this->password, $this->salt)
            = unserialize($serialized, array('allowed_classes' => false));
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

    public function getTokenId(): string
    {
        return sprintf(sprintf('%s-%d', sha1('user_token'), $this->getId()));
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

    public function getLastLoggedAt(): ?\DateTimeInterface
    {
        return $this->lastLoggedAt;
    }

    public function setLastLoggedAt(?\DateTimeInterface $lastLoggedAt): self
    {
        $this->lastLoggedAt = $lastLoggedAt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function refreshLastLoggedAt(): self
    {
        $this->lastLoggedAt = new \DateTime('now');

        return $this;
    }
}
