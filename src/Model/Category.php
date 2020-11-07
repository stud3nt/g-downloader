<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;

class Category extends AbstractModel
{
    /** @ObjectVariable(type="integer") */
    public int $id = 0;

    /** @ObjectVariable(type="string") */
    public ?string $name = null;

    /** @ObjectVariable(type="string") */
    public ?string $label = null;

    /** @ObjectVariable(type="string") */
    public ?string $symbol = null;

    /** @ObjectVariable(type="boolean") */
    public bool $active = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this;
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this;
     */
    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this;
     */
    public function setLabel(string $label = null): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     * @return $this;
     */
    public function setSymbol(string $symbol = null): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return $this;
     */
    public function setActive(bool $active = false): self
    {
        $this->active = $active;

        return $this;
    }
}