<?php

namespace App\Controller\Front\Base;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    protected function validateUser() : bool
    {
        return true;
    }
}