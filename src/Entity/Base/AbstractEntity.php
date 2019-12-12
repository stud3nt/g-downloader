<?php

namespace App\Entity\Base;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @EntityVariable(convertable=true, writable=false, readable=true, inAllConvertNames=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}