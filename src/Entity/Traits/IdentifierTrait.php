<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;

trait IdentifierTrait
{
    /**
     * @ORM\Column(name="identifier", type="string", length=64, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $identifier;

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}
