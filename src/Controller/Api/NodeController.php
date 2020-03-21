<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Factory\ParsedNodeFactory;
use App\Factory\ParserRequestFactory;
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
    public function updateNode(Request $request, ParserService $parserService) : JsonResponse
    {
        $parserRequest = (new ParserRequestFactory())->buildFromRequestData(
            $request->request->all()
        );

        $nodeModel = $parserRequest->getCurrentNode();

        $this->nodeManager->updateNodeInDatabase(
            $this->modelConverter->convert($nodeModel)
        );

        $parserService->clearParserRequestCache($parserRequest, $this->getUser());

        return $this->json(
            $this->modelConverter->convert($nodeModel)
        );
    }
}