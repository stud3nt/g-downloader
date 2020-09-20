<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UrlTrait
{
    /**
     * @ORM\Column(name="url", type="string", length=2048, nullable=false)
     * @Groups("basic_data")
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
