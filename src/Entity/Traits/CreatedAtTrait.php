<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedAtTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"}, nullable=true)
     * @EntityVariable(convertable=true, writable=false, inAllConvertNames=false, readable=true, converter="DateTime")
     * @Groups("user_data")
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
