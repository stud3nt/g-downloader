<?php

namespace App\Entity\Traits;

trait LastViewedAtTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_viewed_at", type="datetime", nullable=true)
     * @EntityVariable(convertable=true, writable=false, readable=false)
     */
    protected $lastViewedAt;

    /**
     * @return \DateTime
     */
    public function getLastViewedAt(): \DateTime
    {
        return $this->lastViewedAt;
    }

    /**
     * @param \DateTime $lastViewedAt
     */
    public function setLastViewedAt(\DateTime $lastViewedAt): void
    {
        $this->lastViewedAt = $lastViewedAt;
    }

    /**
     * Updates last viewed date
     */
    public function refreshLastViewedAt() : void
    {
        $this->lastViewedAt = new \DateTime();
    }
}