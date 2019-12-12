<?php

namespace App\Entity;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\{
    CreatedAtTrait,
    DescriptionTrait,
    NameTrait,
    UpdatedAtTrait
};
use App\Enum\SettingsGroups;
use App\Enum\SettingsLevels;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("settings")
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 */
class Setting extends AbstractEntity
{
    use NameTrait,
        DescriptionTrait,
        CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @ORM\Column(name="group_name", type="string", length=32, nullable=false)
     * @EntityVariable(convertable=true, convertNames={"full_settings_data"}, inAllConvertNames=false, writable=true, readable=true)
     */
    protected $group = SettingsGroups::Common;

    /**
     * @ORM\Column(name="level", type="integer", nullable=false)
     * @EntityVariable(convertable=true, convertNames={"full_settings_data"}, inAllConvertNames=false, writable=true, readable=true)
     */
    protected $level = SettingsLevels::System;

    /**
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $label;

    /**
     * @ORM\Column(name="value", type="text", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $value;

    public function __construct()
    {
        if (!$this->id) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }
}
