<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Factory\Model\ParserRequestFactory;
use App\Manager\CategoryManager;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Manager\TagManager;
use App\Service\ParserService;
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
    /** @var FileManager */
    protected $fileManager;

    /**
     * Execute parser action
     *
     * @Route("/api/parsers/parsing_action", name="api_parsers_action", options={"expose"=true}, methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function parsingAction(Request $request, ParserService $parserService, CategoryManager $categoryManager, TagManager $tagManager) : JsonResponse
    {
        $parserRequest = (new ParserRequestFactory())->buildFromRequestData(
            $request->request->all()
        );

        //if ($parserRequest->getStatus()->checkIfRequestDuplicated())
          //  return $this->jsonError('REQUEST_DUPLICATED');
        //else
            $parserRequest->getStatus()->start();

        $fileManager = $this->container->get(FileManager::class);
        $nodeManager = $this->container->get(NodeManager::class);

        try {
            $nodeManager->completeCurrentNodeDataFromDb($parserRequest);
            $parserService->executeRequestedAction($parserRequest, $this->getCurrentUser());

            $nodeManager->completeParsedNodes($parserRequest); // complete nodes statuses from db data;
            $fileManager->completeParsedStatuses($parserRequest); // complete files statuses from db data;
            $categoryManager->completeCategoriesList($parserRequest); // complete categories list from db;
            $tagManager->completeTagsList($parserRequest); // complete tags list from DB

            $parserRequest->setIgnoreCache(false)->getStatus()->end();

            return $this->json(
                $this->objectSerializer->serialize($parserRequest)
            );
        } catch (\Exception $ex) {
            $parserRequest->getStatus()->end();
            throw $ex;
        }
    }
}