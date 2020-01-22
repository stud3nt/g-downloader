<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Enum\CacheType;
use App\Service\FileCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/api/user/progress", name="api_user_operation_progress", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function pageProgress(): JsonResponse
    {
        $fileCache = new FileCache($this->getUser());

        return $this->jsonSuccess(
            $fileCache->get(CacheType::PageLoaderStatus, [
                'progress' => 1,
                'description' => ''
            ])
        );
    }

    /**
     * @Route("/api/user/progress_reset", name="api_user_reset_operation_progress", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function pageProgressReset(): JsonResponse
    {
        $fileCache = new FileCache($this->getUser());
        $fileCache->remove(CacheType::PageLoaderStatus);

        return $this->jsonSuccess();
    }

    /**
     * @Route("/api/user/clear_cache", name="api_user_clear_cache", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function clearCache(): JsonResponse
    {
        $fileCache = new FileCache($this->getUser());
        $fileCache->removeAll();

        return $this->jsonSuccess();
    }
}