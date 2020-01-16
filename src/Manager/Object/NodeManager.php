<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeLevel;
use App\Enum\NodeStatus;
use App\Manager\Base\EntityManager;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
use App\Repository\NodeRepository;
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

    /**
     * Completes node object with database data (if exists);
     *
     * @param ParserRequest $parserRequest
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
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     */
    public function completeParsedNodes(ParserRequest &$parserRequest): ParserRequest
    {
        $parentNodeEntity = $this->repository->findOneByParsedNode(
            $parserRequest->getCurrentNode()
        );

        if ($parsedNodes = $parserRequest->getParsedNodes()) {
            $parsedNodesIdentifiers = [];
            $parsedNodesForSave = $parsedNodes;

            foreach ($parsedNodes as $parsedNode) { // collect identifiers
                $parsedNodesIdentifiers[] = $parsedNode->getIdentifier();
            }

            $savedNodes = $this->repository->findSavedNodesByRequestAndIdentifiers($parserRequest, $parsedNodesIdentifiers);

            if ($savedNodes) {
                /** @var Node $savedNode */
                /** @var ParsedNode $parsedNode */
                foreach ($parsedNodes as $parsedNodeKey => $parsedNode) {
                    foreach ($savedNodes as $savedNodeKey => $savedNode) { // update statuses
                        if ($savedNode->getIdentifier() == $parsedNode->getIdentifier()) {
                            unset($parsedNodesForSave[$parsedNodeKey]); // no need to save this node, update only;
                            $savedNode->refreshLastViewedAt();

                            if ($savedNode->getImagesNo() !== $parsedNode->getImagesNo()) {
                                $savedNode->setImagesNo($parsedNode->getImagesNo());
                                $savedNode->setRatio($parsedNode->getRatio());
                                $savedNode->setCommentsNo($parsedNode->getCommentsNo());
                                $savedNode->setParentNode($parentNodeEntity);

                                // more images? Adding 'new content' info;
                                $parsedNodes[$parsedNodeKey]->addStatus(NodeStatus::NewContent);
                            }

                            $this->em->persist($savedNode);

                            foreach (NodeStatus::getData() as $status) {
                                $statusGetter = 'get'.ucfirst($status);

                                if (method_exists($savedNode, $statusGetter) && $savedNode->$statusGetter()) {
                                    $parsedNodes[$parsedNodeKey]->addStatus($status);
                                    $parsedNodes[$parsedNodeKey][$status] = $status;
                                }
                            }
                        }
                    }
                }

                usort($parsedNodes, function(ParsedNode $node1, ParsedNode $node2) : int { // sorting nodes - favorites on top
                    if ($node1->isFavorited() === $node2->isFavorited()) {
                        return 0;
                    }

                    return ((int)$node1->isFavorited() > (int)$node2->isFavorited()) ? -1 : 1;
                });

                $parserRequest->setParsedNodes($parsedNodes);
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
