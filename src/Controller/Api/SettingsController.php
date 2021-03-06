<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Manager\DownloadManager;
use App\Manager\SettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends Controller
{
    /**
     * @Route("/api/settings/data", name="api_settings_load", options={"expose"=true}, methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function load(Request $request) : JsonResponse
    {
        $settingsManager = $this->get(SettingsManager::class);

        return $this->json(
            $settingsManager->getAllSettings()
        );
    }

    /**
     * @Route("/api/settings/save", name="api_settings_save", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function save(Request $request) : JsonResponse
    {
        var_dump($request->request->all());
        die();

        return $this->json($this->get(DownloadManager::class)->addFileToQueue());
    }
}