<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait IdentifierTrait
{
    /**
     * @ORM\Column(name="identifier", type="string", length=64, nullable=false)
     * @Groups("basic_data")
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
