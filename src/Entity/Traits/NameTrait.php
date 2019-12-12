<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;

trait NameTrait
{
    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
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
