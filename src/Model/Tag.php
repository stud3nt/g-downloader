<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;

class Tag extends AbstractModel
{
    /** @ObjectVariable(type="integer") */
    public ?int $id = null;

    /** @ObjectVariable (type="string") */
    public ?string $name = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}