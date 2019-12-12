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

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @param string $password
     *
     * @return $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @param string $username
     *
     * @return $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}
