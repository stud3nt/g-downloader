<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Manager\DownloadManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DownloaderController extends Controller
{
    /**
     * Get downloading status
     *
     * @Route("/api/downloader/status", name="downloader_status", options={"expose"=true}, methods={"GET"})
     */
    public function checkDownloadStatus(Request $request) : JsonResponse
    {
        // TODO: CHECK TOKEN;

        $status = $this->get(DownloadManager::class)->downloadStatus();

        return $this->json($status);
    }

    /**
     * Toggle file preview;
     *
     * @Route("/api/downloader/preview_file", name="downloader_preview_file", options={"expose"=true}, methods={"GET"})
     */
    public function previewFile(Request $request) : JsonResponse
    {
        // TODO: CHECK TOKEN;

        $file = $this->getRequestParam($request, 'file');
        $parser = $this->getParser($file['parser']);
        $parser->preparePreview($file);

        return $this->json($file);
    }

    /**
     * Adding file to queue
     *
     * @Route("/api/downloader/process_file", name="downloader_process_file", options={"expose"=true}, methods={"POST"})
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function processFile(Request $request) : JsonResponse
    {
        // TODO: CHECK TOKEN;

        $file = $this->getRequestParam($request, 'file');

        $this->get(FileManager::class)->addFileToQueue($file);

        return $this->json($file);
    }

    /**
     * Starting download process
     *
     * @Route("/api/downloader/download_start", name="downloader_start", options={"expose"=true}, methods={"POST"})
     */
    public function startDownloadProcess() : JsonResponse
    {
        // TODO: CHECK TOKEN;

        return $this->json([
            'status' => $this->get(DownloadManager::class)->downloadStart()
        ]);
    }

    /**
     * Stopping download process
     *
     * @Route("/api/downloader/download_stop", name="downloader_stop", options={"expose"=true}, methods={"POST"})
     */
    public function stopDownloadProcess() : JsonResponse
    {
        // TODO: CHECK TOKEN;

        return $this->json([
            'status' => $this->get(DownloadManager::class)->downloadStop()
        ]);
    }

    /**
     * Execute download action
     *
     * @Route("/api/downloader/download_process", name="downloader_download_process", options={"expose"=true}, methods={"POST"})
     */
    public function downloadProcess(Request $request) : JsonResponse
    {
        // TODO: CHECK TOKEN;

        return $this->json([
            'result' => $this->get(DownloadManager::class)->downloadProcess()
        ]);
    }
}