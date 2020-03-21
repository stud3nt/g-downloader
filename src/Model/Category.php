<?php

namespace App\Model;

use App\Annotation\ModelVariable;

class Category extends AbstractModel
{
    /**
     * @var integer
     * @ModelVariable(type="integer")
     */
    public $id;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $name;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $label;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $symbol;

    /**
     * @var boolean
     * @ModelVariable(type="boolean")
     */
    public $active;

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
     * @return string
     */
    public function getName(): string
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
     * @return string
     */
    public function getLabel(): string
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
     * @return string
     */
    public function getSymbol(): string
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
     * @return string
     */
    public function getActive(): string
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