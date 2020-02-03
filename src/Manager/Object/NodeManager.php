<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeStatus;
use App\Manager\Base\EntityManager;
use App\Model\AbstractModel;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Repository\NodeRepository;
use App\Utils\StringHelper;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ReflectionException;

class NodeManager extends EntityManager
{
    protected $entityName = 'Parser\Node';

    /** @var NodeRepository */
    protected $repository;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var ModelConverter */
    protected $modelConverter;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage, EntityConverter $entityConverter)
    {
        parent::__construct($em, $tokenStorage);

        $this->entityConverter = $entityConverter;
        $this->modelConverter = new ModelConverter();
    }

    public function getOneByParsedNode(ParsedNode $node): ?Node
    {
        return $this->repository->findOneByParsedNode($node);
    }

    /**
     * Completes node object with database data (if exists);
     *
     * @param ParserRequest|AbstractModel $parserRequest
     * @return ParserRequest
     * @throws ReflectionException
     */
    public function completeCurrentNodeDataFromDb(ParserRequest &$parserRequest): ParserRequest
    {
        $node = $parserRequest->getCurrentNode();

        if (!$node->getUrl()) { // node haven't specified url => node doesn't come from database;
            $savedNode = $this->repository->findOneByParsedNode($node);

            if ($savedNode) {
                $nodeArray = $this->entityConverter->convert($savedNode);
                $this->modelConverter->setData($nodeArray, $node);
                $parserRequest->currentNode = $node;
            }
        }

        return $parserRequest;
    }

    /**
     * Update existing node (or creates new if not exists);
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws ReflectionException
     */
    public function updateCurrentNode(ParserRequest &$parserRequest): ParserRequest
    {
        $currentNode = $parserRequest->getCurrentNode();

        if ($currentNode->hasMinimumEntityData()) {
            $savedNode = $this->repository->findOneByParsedNode($currentNode);

            if (!$savedNode) {
                $savedNode = new Node();
            }

            $this->entityConverter->setData($currentNode, $savedNode);
            $this->save($savedNode);
        }
    }

    /**
     * Loads and compares data in nodes from database.
     * Saves new nodes.
     *
     * @param ParserRequest|AbstractModel $parserRequest
     * @return ParserRequest
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function completeParsedNodes(ParserRequest &$parserRequest): ParserRequest
    {
        $parentNode = $parserRequest->getCurrentNode();
        $parentNodeEntity = $this->repository->findOneByParsedNode($parentNode);

        // complete statuses for parent node;
        if ($parentNodeEntity)
            $this->updateNodeStatuses($parentNode, $parentNodeEntity);

        $parserRequest->setCurrentNode($parentNode);

        if ($parsedNodes = $parserRequest->getParsedNodes()) {
            $parsedNodesIdentifiers = [];
            $parsedNodesForSave = $parsedNodes;

            foreach ($parsedNodes as $parsedNode) { // collect identifiers
                $parsedNodesIdentifiers[] = $parsedNode->getIdentifier();
            }

            $savedNodes = $this->repository->findByParentAndIdentifiers($parentNodeEntity, $parsedNodesIdentifiers);

            if ($savedNodes) {
                /** @var Node $savedNode */
                /** @var ParsedNode $parsedNode */
                foreach ($parsedNodes as $parsedNodeKey => $parsedNode) {
                    foreach ($savedNodes as $savedNodeKey => $savedNode) { // update statuses
                        if ($savedNode->getIdentifier() == $parsedNode->getIdentifier()) {
                            unset($parsedNodesForSave[$parsedNodeKey]); // no need to save this node, update only;

                            if ($parentNodeEntity) {
                                $savedNode->setParentNode($parentNodeEntity);
                            }

                            $this->updateNodeStatuses($parsedNode, $savedNode);
                            $this->em->persist($savedNode);

                            $parsedNodes[$parsedNodeKey] = $parsedNode;
                        }
                    }
                }

                // set and sort parsed nodes;
                $parserRequest->setParsedNodes($parsedNodes)->sortParsedNodesByStatus(['favorited' => 'DESC']);
            }

            if ($parsedNodesForSave) {
                foreach ($parsedNodesForSave as $parsedNodeForSave) {
                    $nodeEntity = new Node();
                    $nodeEntity->setParentNode($parentNodeEntity);

                    $this->entityConverter->setData($parsedNodeForSave, $nodeEntity);
                    $this->em->persist($nodeEntity);
                }
            }

            $this->em->flush();
        }

        return $parserRequest;
    }

    public function updateNodeStatuses(ParsedNode &$parsedNode, Node &$savedNode): void
    {
        $savedNode->refreshLastViewedAt();

        if ($savedNode->getImagesNo() !== $parsedNode->getImagesNo()) {
            $savedNode->setImagesNo($parsedNode->getImagesNo());
            $savedNode->setRatio($parsedNode->getRatio());
            $savedNode->setCommentsNo($parsedNode->getCommentsNo());

            // more images? Adding 'new content' info;
            $parsedNode->addStatus(NodeStatus::NewContent);
        }

        foreach (NodeStatus::getData() as $status) {
            $statusGetter = 'get'.ucfirst(StringHelper::underscoreToCamelCase($status));
            $statusSetter = 'set'.ucfirst(StringHelper::underscoreToCamelCase($status));

            if (method_exists($savedNode, $statusGetter) && $savedNode->$statusGetter()) {
                $parsedNode->addStatus($status, true);
                $parsedNode->$statusSetter($savedNode->$statusGetter());
            }
        }
    }

    /**
     * Updates node in database. If node does'nt exists - creates them;
     *
     * @param array $nodeData
     * @return bool
     * @throws \ReflectionException
     */
    public function updateNodeInDatabase(array $nodeData): void
    {
        // TODO: switch node data from array to ParsedNode model;
        $dbNode = $this->repository->findOneBy([
            'identifier' => $nodeData['identifier'],
            'parser' => $nodeData['parser'],
            'level' => $nodeData['level']
        ]);

        if (!$dbNode) {
            $dbNode = new Node();
        }

        $this->entityConverter->setData($nodeData, $dbNode);
        $this->save($dbNode);
    }
}
