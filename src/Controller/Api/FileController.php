<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Manager\Object\FileManager;
use App\Model\ParsedFile;
use App\Parser\Base\ParserInterface;
use App\Utils\StringHelper;
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
    public function toggleFileQueue(Request $request) : JsonResponse
    {
        $parsedFile = new ParsedFile();
        $modelConverter = $this->get(ModelConverter::class);
        $modelConverter->setData($request->request->all(), $parsedFile);

        /** @var ParserInterface $parser */
        $parserName = 'App\\Parser\\'.ucfirst(StringHelper::underscoreToCamelCase($parsedFile->getParser())).'Parser';
        $parser = class_exists($parserName) ? $this->get($parserName) : null;

        if ($parsedFile) {
            $parser->getFileData($parsedFile);
        }

        $this->get(FileManager::class)->toggleFileQueue($parsedFile);

        return $this->json(
            $modelConverter->convert($parsedFile)
        );
    }

    /**
     * Get downloading status
     *
     * @Route("/api/file/toggle_preview", name="api_file_toggle_preview", options={"expose"=true}, methods={"POST"})
     * @throws \Exception
     */
    public function toggleFilePreview(Request $request) : JsonResponse
    {
        $parsedFile = new ParsedFile();
        $modelConverter = $this->get(ModelConverter::class);
        $modelConverter->setData($request->request->all(), $parsedFile);

        /** @var ParserInterface $parser */
        $parserName = 'App\\Parser\\'.ucfirst(StringHelper::underscoreToCamelCase($parsedFile->getParser())).'Parser';
        $parser = class_exists($parserName) ? $this->get($parserName) : null;

        if ($parsedFile) {
            $parser->getFilePreview($parsedFile);
        }

        $parsedFile->setHtmlPreview(
            $this->get('twig')->render('file_preview/'.$parsedFile->getType().'.html.twig', [
                'parsedFile' => $parsedFile
            ])
        );

        return $this->json(
            $modelConverter->convert($parsedFile)
        );
    }
}