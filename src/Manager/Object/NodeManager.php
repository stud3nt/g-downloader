<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeStatus;
use App\Manager\Base\EntityManager;
use App\Model\ParserRequestModel;
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
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     * @throws ReflectionException
     */
    public function completeCurrentNodeDataFromDb(ParserRequestModel &$parserRequestModel): ParserRequestModel
    {
        $node = $parserRequestModel->currentNode;

        if (!$node->getUrl()) { // node haven't specified url => node doesn't come from database;
            $savedNode = $this->repository->findOneBy([
                'parser' => $node->getParser(),
                'level' => $node->getLevel(),
                'identifier' => $node->getIdentifier()
            ]);

            if ($savedNode) {
                $nodeArray = $this->entityConverter->convert($savedNode);
                $this->modelConverter->setData($nodeArray, $node);
                $parserRequestModel->currentNode = $node;
            }
        }

        return $parserRequestModel;
    }

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function completeParsedNodesStatuses(ParserRequestModel &$parserRequestModel): ParserRequestModel
    {
        if ($parsedNodes = $parserRequestModel->parsedNodes) {
            $parsedNodesIdentifiers = [];

            foreach ($parsedNodes as $parsedNode) { // collect identifiers
                $parsedNodesIdentifiers[] = $parsedNode['identifier'];
            }

            $savedNodes = $this->repository->findBy([
                'identifier' => $parsedNodesIdentifiers,
                'parser' => $parserRequestModel->currentNode->parser,
                'level' => $parserRequestModel->currentNode->nextLevel ?? $parserRequestModel->currentNode->level
            ]);

            if ($savedNodes) {
                /** @var Node $savedNode */
                foreach ($parsedNodes as $parsedNodeKey => $parsedNode) {
                    foreach ($savedNodes as $savedNodeKey => $savedNode) { // update statuses
                        if ($savedNode->getIdentifier() == $parsedNode['identifier']) {
                            $savedNode->refreshLastViewedAt();

                            if ($savedNode->getImagesNo() !== $parsedNode['imagesNo']) {
                                $savedNode->setImagesNo($parsedNode['imagesNo']);
                                $savedNode->setRatio($parsedNode['ratio']);
                                $savedNode->setCommentsNo($parsedNode['commentsNo']);

                                $parsedNodes[$parsedNodeKey]['statuses'][] = NodeStatus::NewContent;
                            }

                            $this->em->persist($savedNode);

                            foreach (NodeStatus::getData() as $status) {
                                $statusGetter = 'get'.ucfirst($status);

                                if (method_exists($savedNode, $statusGetter) && $savedNode->$statusGetter()) {
                                    $parsedNodes[$parsedNodeKey]['statuses'][] = $status;
                                    $parsedNodes[$parsedNodeKey][$status] = $status;
                                }
                            }
                        }
                    }
                }

                usort($parsedNodes, function($node1, $node2) : int { // sorting nodes - favorites on top
                    if ($node1['favorited'] === $node2['favorited']) {
                        return 0;
                    }

                    return ($node1['favorited'] > $node2['favorited']) ? -1 : 1;
                });

                $this->em->flush();

                $parserRequestModel->parsedNodes = $parsedNodes;
            }
        }

        return $parserRequestModel;
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
