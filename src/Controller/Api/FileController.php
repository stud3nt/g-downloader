<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Enum\FileStatus;
use App\Factory\ParsedFileFactory;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Model\ParsedFile;
use App\Service\DownloadService;
use App\Service\ParserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends Controller
{
    /**
     * Get downloading status
     *
     * @Route("/api/file/toggle_queue", name="api_file_toggle_queue", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
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
        $user = $this->getCurrentUser();

        $parser = $parserService->loadParser(
            $parsedFile->getParser(),
            $user
        );
        $parser->getFileData($parsedFile);

        if ($queuedFile = $fileManager->getFileEntityByParsedFile($parsedFile)) { // file exists => removing...
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
     * @IsGranted("ROLE_ADMIN")
     * @Route("/api/file/toggle_preview", name="api_file_toggle_preview", options={"expose"=true}, methods={"POST"})
     * @throws \Exception
     */
    public function toggleFilePreview(Request $request, ParserService $parserService) : JsonResponse
    {
        $parsedFile = $this->prepareParsedFilePreview($request, $parserService);
        $parsedFile->setHtmlPreview(
            $this->get('twig')->render('file_preview/'.$parsedFile->getType().'.html.twig', [
                'parsedFile' => $parsedFile
            ])
        );

        return $this->json(
            $this->get(ModelConverter::class)->convert($parsedFile)
        );
    }

    /**
     *
     * @Route("/api/file/save_previewed_file", name="api_file_download_preview", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param ParserService $parserService
     * @param FileManager $fileManager
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function savePreviewedFile(Request $request, ParserService $parserService, DownloadService $downloadService, FileManager $fileManager, NodeManager $nodeManager): JsonResponse
    {
        $parsedFile = $this->prepareParsedFilePreview($request, $parserService);
        $parsedFile->addStatus(FileStatus::Queued);

        $fileEntity = $fileManager->getFileEntityByParsedFile($parsedFile, true);
        $fileEntity->setParentNode(
            $nodeManager->getOneByParsedNode(
                $parsedFile->getParentNode()
            )
        );

        if ($downloadService->downloadFileByEntity($fileEntity, $this->getCurrentUser()))
            $parsedFile->addStatus(FileStatus::Downloaded);

        $fileManager->save($fileEntity);

        return $this->json(
            $this->get(ModelConverter::class)->convert($parsedFile)
        );
    }

    /**
     * @param Request $request
     * @param ParserService $parserService
     * @return ParsedFile
     * @throws \ReflectionException
     */
    protected function prepareParsedFilePreview(Request $request, ParserService $parserService): ParsedFile
    {
        $parsedFile = (new ParsedFileFactory())->buildFromRequestData(
            $request->request->all()
        );
        $parser = $parserService->loadParser(
            $parsedFile->getParser(),
            $this->getCurrentUser()
        );
        $parser->getFilePreview($parsedFile);

        return $parsedFile;
    }
}