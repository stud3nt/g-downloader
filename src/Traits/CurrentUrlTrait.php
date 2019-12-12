<?php

namespace App\Traits;

trait CurrentUrlTrait
{
    protected $currentUrl;

    public function getCurrentUrl() : string
    {
        return $this->currentUrl;
    }
}