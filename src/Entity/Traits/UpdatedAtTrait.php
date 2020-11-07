<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use App\Annotation\Serializer\ObjectVariable;

trait UpdatedAtTrait
{   
    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @ObjectVariable(type="datetime", writable=false)
     */
    protected $updatedAt;

    /**
     * Refresh updatedAt field;
     *
     * @return $this
     * @throws \Exception
     */
    public function refreshUpdatedAt()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }
    
    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
