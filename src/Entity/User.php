<?php

namespace App\Entity;

use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Enum\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class User extends AbstractEntity implements UserInterface, \Serializable
{
    use CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
     * @Groups("user_data")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected string $email;

    /**
     * @ORM\Column(name="username", type="string", length=40, nullable=false, unique=true)
     * @Groups("user_data")
     */
    protected string $username;

    /**
     * @ORM\Column(name="name", type="string", length=40, nullable=false, unique=true)
     * @Groups("user_data")
     */
    protected string $name;

    /**
     * @ORM\Column(name="surname", type="string", length=60, nullable=false, unique=true)
     * @Groups("user_data")
     */
    protected ?string $surname;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     * @Assert\Length(min="6")
     */
    protected string $password;

    /**
     * @ORM\Column(name="thumbnail", type="string", nullable=true, length=255)
     * @Groups("user_data")
     */
    protected ?string $thumbnail;

    /**
     * @ORM\Column(name="file_token", type="string", nullable=true, length=32)
     * @Groups("user_data")
     */
    protected ?string $fileToken;

    /**
     * @ORM\Column(name="api_token", type="string", nullable=true, length=32)
     * @Groups("user_data")
     */
    protected ?string $apiToken;

    /**
     * @ORM\Column(name="cache_token", type="string", nullable=true, length=32)
     * @Groups("user_data")
     */
    protected ?string $cacheToken;

    /**
     * @ORM\Column(name="salt", type="string", nullable=false, length=64)
     */
    protected string $salt;

    /**
     * @ORM\Column(name="roles", type="array", nullable=false)
     */
    protected array $roles = [UserRole::Admin];

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected bool $isActive = false;

    /**
     * @ORM\Column(name="last_logged_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $lastLoggedAt;

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

    public function getFileToken(): ?string
    {
        return $this->fileToken;
    }

    public function setFileToken(?string $fileToken): self
    {
        $this->fileToken = $fileToken;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getDownloaderRedisKey(): string
    {
        return 'downloader_data_'.$this->getApiToken();
    }

    public function getCacheToken(): ?string
    {
        return $this->cacheToken;
    }

    public function setCacheToken(?string $cacheToken): self
    {
        $this->cacheToken = $cacheToken;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }
}
