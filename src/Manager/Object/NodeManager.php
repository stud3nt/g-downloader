<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeStatus;
use App\Manager\Base\EntityManager;
use App\Model\ParsedNode;
use App\Model\ParserRequestModel;
use App\Repository\NodeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NodeManager extends EntityManager
{
    protected $entityName = 'Parser\Node';

    /** @var NodeRepository */
    protected $repository;

    /** @var EntityConverter */
    protected $entityConverter;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage, EntityConverter $entityConverter)
    {
        parent::__construct($em, $tokenStorage);

        $this->entityConverter = $entityConverter;
    }

    /**
     * @param ParserRequestModel $parserRequestModel
     * @return ParserRequestModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function completeParsedStatuses(ParserRequestModel &$parserRequestModel) : ParserRequestModel
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
                foreach ($savedNodes as $savedNodeKey => $savedNode) { // update statuses
                    foreach ($parsedNodes as $parsedNodeKey => $parsedNode) {
                        if ($savedNode->getIdentifier() == $parsedNode['identifier']) {
                            $parsedNodes[$parsedNodeKey]['statuses'][] = NodeStatus::Queued;
                            $savedNode->refreshLastViewedAt();

                            $this->em->persist($savedNode);
                        }

                        foreach (NodeStatus::getData() as $status) {
                            $statusGetter = 'get'.ucfirst($status);

                            if (method_exists($savedNode, $statusGetter) && $savedNode->$statusGetter()) {
                                $parsedNodes[$parsedNodeKey]['statuses'][] = $status;
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
     * Saves Node in database;
     *
     * @param ParsedNode $node
     * @return ParsedNode
     * @throws \ReflectionException
     * @throws ORMException
     */
    public function toggleNodeDatabase(ParsedNode &$node) : ?ParsedNode
    {
        $dbNode = $this->findNode($node);

        if (!$dbNode) {
            $dbNode = new Node();

            $this->entityConverter->setData($node, $dbNode);
            $this->save($dbNode);

            return $node;
        } else {
            $this->remove($dbNode);
        }

        return null;
    }

    public function findNode(ParsedNode $node) : ?Node
    {
        return $this->repository->findOneBy([
            'identifier' => $node->identifier,
            'parser' => $node->parser,
            'level' => $node->level
        ]);
    }
}
