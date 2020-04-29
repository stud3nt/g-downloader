<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Enum\DownloaderStatus;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Service\DownloadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DownloaderController extends Controller
{
    /**
     * Starting/continue download files process
     *
     * @Route("/api/downloader/process", name="api_start_downloader_process", options={"expose"=true}, methods={"GET"})
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
    public function downloadProcess(DownloadService $downloadService, FileManager $fileManager, DownloadManager $downloadManager): JsonResponse
    {
        $filesForDownload = $fileManager->getQueuedFiles(6);
        $user = $this->getUser();

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
     * @Route("/api/downloader/stop", name="api_stop_downloader_process", options={"expose"=true}, methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param DownloadManager $downloadManager
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \ReflectionException
     */
    public function stopDownload(DownloadManager $downloadManager): JsonResponse
    {
        try {
            $downloadManager->createStatusData($this->getUser(), DownloaderStatus::Idle, []);

            return $this->jsonSuccess();
        } catch (\Exception $ex) {
            return $this->jsonSuccess(
                $ex->getMessage()
            );
        }
    }
}