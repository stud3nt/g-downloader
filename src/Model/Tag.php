<?php

namespace App\Model;

use App\Annotation\ModelVariable;

class Tag extends AbstractModel
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}