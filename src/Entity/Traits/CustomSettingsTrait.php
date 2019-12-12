<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CustomSettingsTrait
{
    /**
     * @ORM\Column(name="custom_settings", type="array", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $customSettings;
}
