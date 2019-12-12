<?php

namespace App\Entity\Traits;

use App\Annotation\EntityVariable;
use Doctrine\ORM\Mapping as ORM;

trait UrlTrait
{
    /**
     * @ORM\Column(name="url", type="string", length=2048, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $url;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
