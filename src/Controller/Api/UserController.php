<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Entity\User;
use App\Enum\CacheType;
use App\Service\FileCache;
use App\Utils\CacheHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/api/user/progress", name="api_user_operation_progress", methods={"GET"}, options={"expose":true})
     */
    public function pageProgress() : JsonResponse
    {
        return $this->json(
            $this->get(FileCache::class)->get(CacheType::PageLoaderStatus)
        );
    }

    /**
     * @Route("/api/user/progress_reset", name="api_user_reset_operation_progress", methods={"GET"}, options={"expose":true})
     */
    public function pageProgressReset() : JsonResponse
    {
        $fileCache = $this->get(FileCache::class);
        $fileCache->remove(CacheType::PageLoaderStatus);

        return $this->json([
            'status' => 1
        ]);
    }
}