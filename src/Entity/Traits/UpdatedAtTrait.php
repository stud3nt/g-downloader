<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UpdatedAtTrait
{   
    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @EntityVariable(convertable=true, writable=true, inAllConvertNames=false, readable=true, converter="DateTime")
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
