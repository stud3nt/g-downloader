<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Enum\{NodeLevel};
use App\Factory\ParserRequestFactory;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Model\ParsedNode;
use App\Service\ParserService;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ParserController
 * @package App\Controller\Api
 */
class ParserController extends Controller
{
    /** @var ModelConverter */
    protected $modelConverter;

    /** @var NodeManager */
    protected $nodeManager;

    /** @var FileManager */
    protected $fileManager;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->modelConverter = $this->get(ModelConverter::class);
        $this->nodeManager = $this->get(NodeManager::class);
        $this->fileManager = $this->get(FileManager::class);
    }

    /**
     * Execute parser action
     *
     * @Route("/api/parsers/parsing_action", name="api_parsers_action", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function parsingAction(Request $request, ParserService $parserService) : JsonResponse
    {
        $parserRequest = (new ParserRequestFactory())->buildFromRequestData(
            $request->request->all()
        );

        $this->nodeManager->completeCurrentNodeDataFromDb($parserRequest);

        $parser = $parserService->loadParser($parserRequest->currentNode->parser);

        switch ($parserRequest->currentNode->level) { // execute parser action - load nodes or files;
            case NodeLevel::Owner:
                $parser->getOwnersList($parserRequest);
                break;

            case NodeLevel::BoardsList:
                $parser->getBoardsListData($parserRequest);
                break;

            case NodeLevel::Board:
                $parser->getBoardData($parserRequest);
                break;

            case NodeLevel::Gallery:
                $parser->getGalleryData($parserRequest);
                break;
        }

        $this->nodeManager->completeParsedNodes($parserRequest);
        $this->fileManager->completeParsedStatuses($parserRequest);

        $parserRequest->ignoreCache = false;

        return $this->json(
            $this->modelConverter->convert($parserRequest)
        );
    }

    /**
     * Marks node statuses;
     *
     * @Route("/api/parsers/mark_node", name="api_parsers_mark_node", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws ORMException
     */
    public function markNode(Request $request) : JsonResponse
    {
        $nodeModel = new ParsedNode();
        $this->modelConverter->setData($request->request->all(), $nodeModel);
        $nodeModel->setStatusesFromArray();

        $this->nodeManager->updateNodeInDatabase(
            $this->modelConverter->convert($nodeModel)
        );

        return $this->json(
            $this->modelConverter->convert($nodeModel)
        );
    }
}