<?php

namespace App\Controller\Front\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    protected function validateUser() : bool
    {
        return true;
    }
}