<?php

namespace App\Controller\Front;

use App\Controller\Front\Base\Controller;
use App\Service\AngularConfigService;
use App\Utils\AppHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="app_index", options={"expose"=true})
     * @Route("/login", name="app_login", options={"expose"=true})
     * @Route("/logout", name="app_logout", options={"expose"=true})
     * @Route(
     *     "/parsers/{parserName}/{nodeLevel}/{nodeIdentifier}",
     *     name="app_parser",
     *     defaults={"parserName":null, "nodeLevel":null, "nodeIdentifier":null},
     *     options={"expose"=true}
     * )
     * @Route("/tools/{toolName}", name="app_tools", defaults={"toolName":null}, options={"expose"=true})
     * @Route("/settings", name="app_settings", options={"expose"=true})
     * @Route("/users/list", name="app_users_list", options={"expose"=true})
     * @Route("/users/list/edit/{userToken}", name="app_users_editor", options={"expose"=true})
     * @Route("/users/groups", name="app_users_groups", options={"expose"=true})
     * @Route("/lists/{listName}", name="app_lists", options={"expose"=true})
     * @Route("/download/list", name="app_download_list", options={"expose"=true})
     *
     * @throws \Exception
     */
    public function start(AngularConfigService $angularConfigService)
    {
        $angularConfigService->generateInitialJsonConfigFile();

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