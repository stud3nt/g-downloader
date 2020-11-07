<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use App\Annotation\Serializer\ObjectVariable;

trait CreatedAtTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"}, nullable=true)
     * @ObjectVariable(type="datetime", writable=false)
     */
    protected $createdAt;
    
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
