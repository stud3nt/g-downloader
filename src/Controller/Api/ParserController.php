<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Converter\ModelConverter;
use App\Enum\{NodeLevel};
use App\Factory\ParsedNodeFactory;
use App\Factory\ParserRequestFactory;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
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

        if ($parserRequest->getStatus()->checkIfRequestDuplicated()) {
            return $this->jsonError('REQUEST_DUPLICATED');
        } else {
            $parserRequest->getStatus()->start();
        }

        $fileManager = $this->container->get(FileManager::class);
        $nodeManager = $this->container->get(NodeManager::class);
        $nodeManager->completeCurrentNodeDataFromDb($parserRequest);

        try {
            $parserService->executeRequestedAction($parserRequest, $this->getUser());
        } catch (\Exception $ex) {
            $parserRequest->getStatus()->end();
            throw $ex;
        }

        $nodeManager->completeParsedNodes($parserRequest); // complete nodes statuses from db data;
        $fileManager->completeParsedStatuses($parserRequest); // complete files statuses from db data;

        $parserRequest->setIgnoreCache(false)
            ->getStatus()
            ->end();

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
        $nodeModel = (new ParsedNodeFactory())->buildFromRequestData($request->request->all());

        $this->nodeManager->updateNodeInDatabase(
            $this->modelConverter->convert($nodeModel)
        );

        return $this->json(
            $this->modelConverter->convert($nodeModel)
        );
    }
}