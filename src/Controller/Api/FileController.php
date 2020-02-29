<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Factory\ParsedFileFactory;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Service\ParserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends Controller
{
    /**
     * Get downloading status
     *
     * @Route("/api/file/toggle_queue", name="api_file_toggle_queue", options={"expose"=true}, methods={"POST"})
     *
     * @param Request $request
     * @param ParserService $parserService
     * @param DownloadManager $downloadManager
     * @param NodeManager $nodeManager
     * @param FileManager $fileManager
     * @return JsonResponse
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function toggleFileQueue(Request $request, ParserService $parserService, NodeManager $nodeManager, DownloadManager $downloadManager, FileManager $fileManager): JsonResponse
    {
        $parsedFile = (new ParsedFileFactory())->buildFromRequestData(
            $request->request->all()
        );
        $user = $this->getUser();

        $parser = $parserService->loadParser(
            $parsedFile->getParser(),
            $user
        );
        $parser->getFileData($parsedFile);

        if ($queuedFile = $fileManager->getQueueFileByParsedFile($parsedFile)) { // file exists => removing...
            $fileManager->removeParsedFileFromQueue($parsedFile, $queuedFile);
            $downloadManager->decreaseQueueByParsedFile($user, $parsedFile);
        } else {
            $parentNode = $nodeManager->getOneByParsedNode($parsedFile->getParentNode()); // file not exists => adding...
            $fileManager->addParsedFileToQueue($parsedFile, $parentNode);
            $downloadManager->increaseQueueByParsedFile($user, $parsedFile);
        }

        return $this->json(
            $this->get(ModelConverter::class)->convert($parsedFile)
        );
    }

    /**
     * Get downloading status
     *
     * @Route("/api/file/toggle_preview", name="api_file_toggle_preview", options={"expose"=true}, methods={"POST"})
     * @throws \Exception
     */
    public function toggleFilePreview(Request $request, ParserService $parserService) : JsonResponse
    {
        $parsedFile = (new ParsedFileFactory())->buildFromRequestData(
            $request->request->all()
        );

        $parser = $parserService->loadParser(
            $parsedFile->getParser(),
            $this->getUser()
        );
        $parser->getFilePreview($parsedFile);
        $parsedFile->setHtmlPreview(
            $this->get('twig')->render('file_preview/'.$parsedFile->getType().'.html.twig', [
                'parsedFile' => $parsedFile
            ])
        );

        return $this->json(
            $this->get(ModelConverter::class)->convert($parsedFile)
        );
    }
}