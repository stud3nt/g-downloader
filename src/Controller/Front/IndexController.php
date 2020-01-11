<?php

namespace App\Controller\Front;

use App\Controller\Front\Base\Controller;
use App\Service\AngularConfigService;
use App\Utils\AppHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class IndexController extends Controller
{
    /**
     * @Route("/", name="app_index", options={"expose"=true})
     * @Route("/login", name="app_login", options={"expose"=true})
     * @Route("/logout", name="app_logout", options={"expose"=true})
     * @Route("/parsers/{parserName}", name="app_parser", defaults={"parserName":null}, options={"expose"=true})
     * @Route("/tools/{toolName}", name="app_tools", defaults={"toolName":null}, options={"expose"=true})
     * @Route("/settings", name="app_settings", options={"expose"=true})
     * @Route("/users/list", name="app_users_list", options={"expose"=true})
     * @Route("/users/list/edit/{userToken}", name="app_users_editor", options={"expose"=true})
     * @Route("/users/groups", name="app_users_groups", options={"expose"=true})
     *
     * @throws \Exception
     */
    public function start()
    {
        $this->get(AngularConfigService::class)->generateInitialJsonConfigFile();

        return $this->render('index.html.twig');
    }

    /**
     * @Route("/favicon.ico", name="app_favicon", options={"expose"=true}, methods={"GET"})
     */
    public function favicon()
    {
        return new BinaryFileResponse(AppHelper::getPublicDir().'app'.DIRECTORY_SEPARATOR.'favicon.ico');
    }

}