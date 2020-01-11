<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Enum\DownloaderStatus;
use App\Manager\Object\FileManager;
use App\Service\DownloadService;
use App\Service\FileCache;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends Controller
{
    /**
     * Get downloading status
     *
     * @Route("/api/downloader/status", name="api_downloader_check_status", options={"expose"=true}, methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function checkStatus(): JsonResponse
    {
        return $this->jsonSuccess(
            array_merge(
                $this->get(FileManager::class)->getBasicFilesData(),
                ['queuedFiles' => $this->get(FileManager::class)->getQueuedFiles(6, true)],
                ['downloaderStatus' => (new FileCache($this->getUser()))->get('downloader_status', DownloaderStatus::Idle)]
            )
        );
    }

    /**
     * Starting download process
     *
     * @Route("/api/downloader/change_status/{statusName}", name="api_downloader_change_status", options={"expose"=true}, methods={"GET"}, defaults={"status":null})
     * @IsGranted("ROLE_ADMIN")
     */
    public function changeStatus(Request $request): JsonResponse
    {
        try {
            $cache = (new FileCache($this->getUser()));
            $cache->set('downloader_status', $request->get('status'));

            return $this->jsonSuccess();
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Downloading files process
     *
     * @Route("/api/downloader/process", name="api_downloader_process", options={"expose"=true}, methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function downloadProcess(DownloadService $downloadService, FileManager $fileManager): JsonResponse
    {
        try {
            $filesForDownload = $fileManager->getQueuedFiles(6);

            if ($filesForDownload) {
                $downloadedFiles = $downloadService->downloadQueuedParserFiles($filesForDownload);

                if ($downloadedFiles) {
                    $fileManager->updateDownloadedFiles($downloadedFiles);

                    return $this->jsonSuccess([
                        'filesCount' => count($downloadedFiles)
                    ]);
                }
            }

            return $this->jsonError();
        } catch (\Exception $ex) {
            return $this->jsonError($ex->getMessage());
        }
    }
}