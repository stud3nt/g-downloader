<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Enum\DownloaderStatus;
use App\Factory\Model\QueueRequestFactory;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Service\DownloadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DownloaderController
 * @Route("/api/downloader", name="api_downloader_")
 * @package App\Controller\Api
 */
class DownloaderController extends Controller
{
    /**
     * @Route("/prepare_queue", name="prepare_queue", options={"expose"=true}, methods={"POST"})
     * @param FileManager $fileManager
     * @return JsonResponse
     */
    public function prepareQueue(Request $request, FileManager $fileManager, QueueRequestFactory $queueSettingsFactory): JsonResponse
    {
        $queueSettings = $queueSettingsFactory->buildFromRequestData($request);

        return $this->jsonSuccess(
            $fileManager->getQueuedFiles($queueSettings->getPerPage() * 5, true)
        );
    }

    /**
     * Starting/continue download files process
     *
     * @Route("/process/{downloadingFilesCount}", name="process", options={"expose"=true}, methods={"GET"}, defaults={"downloadingFilesCount"=6})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param DownloadService $downloadService
     * @param FileManager $fileManager
     * @param DownloadManager $downloadManager
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function downloadProcess(
        Request $request,
        DownloadService $downloadService,
        FileManager $fileManager,
        DownloadManager $downloadManager
    ): JsonResponse {
        $filesForDownload = $fileManager->getFilesForDownload(
            $request->get('downloadingFilesCount', 6)
        );
        $user = $this->getCurrentUser();

        if ($filesForDownload) {
            $downloadManager->createStatusData($user, DownloaderStatus::Downloading, $filesForDownload);
            $downloadedFiles = $downloadService->downloadQueuedParserFiles($filesForDownload, $user);
            $downloadManager->createStatusData($user, DownloaderStatus::Idle, []);

            return $this->jsonSuccess([
                'filesCount' => $downloadedFiles
            ]);
        } else {
            $downloadManager->createStatusData($user, DownloaderStatus::Idle, []);
        }

        return $this->jsonError();
    }

    /**
     * Stops download process
     *
     * @Route("/stop", name="stop", options={"expose"=true}, methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param DownloadManager $downloadManager
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \ReflectionException
     */
    public function stopDownload(DownloadManager $downloadManager): JsonResponse
    {
        try {
            $downloadManager->createStatusData($this->getCurrentUser(), DownloaderStatus::Idle, []);

            return $this->jsonSuccess();
        } catch (\Exception $ex) {
            return $this->jsonSuccess(
                $ex->getMessage()
            );
        }
    }
}