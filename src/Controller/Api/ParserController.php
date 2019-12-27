<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Enum\{NodeLevel};
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Model\ParsedNode;
use App\Model\ParserRequestModel;
use App\Parser\Base\ParserInterface;
use App\Utils\StringHelper;
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