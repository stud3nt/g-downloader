<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;

trait LastContentChangedAtTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_content_change_at", type="datetime", nullable=true)
     * @EntityVariable(convertable=true, writable=false, readable=false)
     */
    protected $lastContentChangeAt;

    /**
     * @return \DateTime
     */
    public function getLastContentChangeAt(): \DateTime
    {
        return $this->lastContentChangeAt;
    }

    /**
     * @param \DateTime $lastContentChangeAt
     */
    public function setLastContentChangeAt(\DateTime $lastContentChangeAt): void
    {
        $this->lastContentChangeAt = $lastContentChangeAt;
    }

    /**
     * Updates last content change date;
     */
    public function refreshLastContentChangedAt() : void
    {
        $this->lastContentChangeAt = new \DateTime();
    }
}