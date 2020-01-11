<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Entity\User;
use App\Enum\CacheType;
use App\Service\FileCache;
use App\Utils\CacheHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/api/user/progress", name="api_user_operation_progress", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function pageProgress() : JsonResponse
    {
        $fileCache = new FileCache($this->getUser());

        return $this->json(
            $fileCache->get(CacheType::PageLoaderStatus)
        );
    }

    /**
     * @Route("/api/user/progress_reset", name="api_user_reset_operation_progress", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     */
    public function pageProgressReset() : JsonResponse
    {
        $fileCache = new FileCache($this->getUser());
        $fileCache->remove(CacheType::PageLoaderStatus);

        return $this->json([
            'status' => 1
        ]);
    }
}