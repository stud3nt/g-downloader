<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;

trait DescriptionTrait
{
    /**
     * @ORM\Column(name="description", type="string", length=2048, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $description;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }
}
