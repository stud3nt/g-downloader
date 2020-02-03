<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Factory\ParsedFileFactory;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Model\ParsedFile;
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
     * @throws \Exception
     */
    public function toggleFileQueue(Request $request, ParserService $parserService) : JsonResponse
    {
        $parsedFile = (new ParsedFileFactory())->buildFromRequestData(
            $request->request->all()
        );

        $parser = $parserService->loadParser(
            $parsedFile->getParser(),
            $this->getUser()
        );
        $parser->getFileData($parsedFile);

        $parentNode = $this->get(NodeManager::class)->getOneByParsedNode($parsedFile->getParentNode());
        $this->get(FileManager::class)->toggleFileQueue($parsedFile, $parentNode);

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