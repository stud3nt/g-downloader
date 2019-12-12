<?php

namespace App\Controller\Front;

use App\Controller\Front\Base\Controller;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="app_login", methods={"GET", "POST"})
     */
    public function login()
    {



        return $this->render('index.html.twig');
    }

    /**
     * @Route("/login", name="app_logout")
     */
    public function logout()
    {
        return $this->render('index.html.twig');
    }
}