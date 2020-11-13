<?php

namespace App\Entity\Traits;

use App\Annotation\Serializer\ObjectVariable;
use Doctrine\ORM\Mapping as ORM;

trait NameTrait
{
    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @ObjectVariable(type="string")
     */
    protected $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
