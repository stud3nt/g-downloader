<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Factory\ParsedNodeFactory;
use App\Factory\ParserRequestFactory;
use App\Manager\CategoryManager;
use App\Manager\TagManager;
use App\Parser\Base\AbstractParser;
use App\Service\ParserService;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ParserController
 * @package App\Controller\Api
 */
class NodeController extends Controller
{
    /**
     * Marks node statuses;
     *
     * @Route("/api/node/update", name="api_node_update", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws ORMException
     */
    public function updateNode(Request $request, ParserService $parserService, CategoryManager $categoryManager, TagManager $tagManager) : JsonResponse
    {
        $parserRequest = (new ParserRequestFactory())->buildFromRequestData(
            $request->request->all()
        );

        $parserRequest->setCurrentNode(
            $this->nodeManager->updateNodeInDatabase(
                $parserRequest->getCurrentNode()
            )
        );

        $categoryManager->completeCategoriesList($parserRequest); // complete categories list from db;
        $tagManager->completeTagsList($parserRequest); // complete tags list from DB

        $parserService->clearParserRequestCache($parserRequest, $this->getUser());

        return $this->json(
            $this->modelConverter->convert($parserRequest)
        );
    }
}