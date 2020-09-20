<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Factory\Model\QueueRequestFactory;
use App\Factory\SerializerFactory;
use App\Manager\Object\FileManager;
use App\Manager\QueueManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class QueueController
 * @Route("/api/queue", name="api_queue_")
 * @package App\Controller\Api
 */
class QueueController extends Controller
{
    /**
     * @Route("/prepare_queue_package", name="prepare_queue_package", options={"expose"=true}, methods={"POST"})
     * @param FileManager $fileManager
     * @return JsonResponse
     */
    public function prepareQueuePackage(
        Request $request,
        QueueManager $queueManager,
        QueueRequestFactory $queueSettingsFactory,
        SerializerFactory $serializerFactory
    ): JsonResponse {
        $serializer = $serializerFactory->getEntityNormalizer();
        $queueRequest = $queueSettingsFactory->buildFromRequestData($request);
        $queueManager->getQueuedFiles($queueRequest);

        return $this->jsonSuccess(
            $serializer->normalize($queueRequest)
        );
    }
}