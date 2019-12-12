<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Enum\{NodeLevel, NodeStatus};
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Model\ParsedNode;
use App\Model\ParserRequestModel;
use App\Parser\Base\ParserInterface;
use App\Utils\StringHelper;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ParserController extends Controller
{
    /** @var ModelConverter */
    protected $modelConverter;

    /** @var NodeManager */
    protected $nodeManager;

    /** @var FileManager */
    protected $fileManager;

    public function __construct()
    {
        $this->modelConverter = $this->get(ModelConverter::class);
        $this->nodeManager = $this->get(NodeManager::class);
        $this->fileManager = $this->get(FileManager::class);
    }

    /**
     * Execute parser action
     *
     * @Route("/api/parsers/parsing_action", name="api_parsers_action", options={"expose"=true}, methods={"POST"})
     * @throws \Exception
     */
    public function parsingAction(Request $request) : JsonResponse
    {
        $parserRequestModel = new ParserRequestModel();
        $modelConverter = $this->get(ModelConverter::class);
        $modelConverter->setData($request->request->all(), $parserRequestModel);

        /** @var ParserInterface $parser */
        $parserName = 'App\\Parser\\'.ucfirst(StringHelper::underscoreToCamelCase($parserRequestModel->parser)).'Parser';
        $parser = class_exists($parserName) ? $this->get($parserName) : null;

        switch ($parserRequestModel->level) { // execute parser action
            case NodeLevel::Owner:
                $parser->loadOwnersList($parserRequestModel);
                break;

            case NodeLevel::BoardsList:
                $parser->getBoardsListData($parserRequestModel);
                break;

            case NodeLevel::Board:
                $parser->getBoardData($parserRequestModel);
                break;

            case NodeLevel::Gallery:
                $parser->getGalleryData($parserRequestModel);
                break;
        }

        $this->nodeManager->completeParsedStatuses($parserRequestModel);
        $this->fileManager->completeParsedStatuses($parserRequestModel);

        $parserRequestModel->ignoreCache = false;

        return $this->json(
            $modelConverter->convert($parserRequestModel)
        );
    }

    /**
     * Mark node as;
     *
     * @Route("/api/parsers/mark_node", name="api_parsers_mark_node", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function markNode(Request $request) : JsonResponse
    {
        $nodeModel = new ParsedNode();
        $nodeStatus = $request->request->get('status');

        $this->modelConverter->setData($request->request->all(), $nodeModel);

        if ($nodeModel->emptyStatuses())


        return $this->json(
            $this->modelConverter->convert($nodeModel)
        );
    }
}