<?php

namespace App\Model;

use App\Annotation\ModelVariable;

class PaginationSelector extends AbstractModel
{
    /**
     * @var string
     * @ModelVariable()
     */
    public $label = 'A';

    /**
     * @var string
     * @ModelVariable()
     */
    public $value = '';

    /**
     * @var bool
     * @ModelVariable(type="boolean")
     */
    public $isActive = false;

    /**
     * @var PaginationSelector[]
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\PaginationSelector"}, type="array")
     */
    public $childrens = [];

    public function __toString()
    {
        return $this->value;
    }

    public function getActiveChildren(): ?PaginationSelector
    {
        if ($this->childrens) {
            foreach ($this->childrens as $children) {
                if ($children->isActive()) {
                    return $children;
                }
            }

            return $this->childrens[0];
        }

        return null;
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
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return PaginationSelector[]
     */
    public function getChildrens(): array
    {
        return $this->childrens;
    }

    /**
     * @param PaginationSelector[] $childrens
     * @return $this
     */
    public function setChildrens(array $childrens): self
    {
        $this->childrens = $childrens;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}