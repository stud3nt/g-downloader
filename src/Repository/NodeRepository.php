<?php

namespace App\Repository;

use App\Entity\Parser\Node;
use App\Model\ParsedNode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Node|null find($id, $lockMode = null, $lockVersion = null)
 * @method Node|null findOneBy(array $criteria, array $orderBy = null)
 * @method Node[] findAll()
 * @method Node[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Node::class);
    }

    /**
     * Founds one node by ParsedNode model
     *
     * @param ParsedNode $node
     * @return Node|null
     */
    public function findOneByParsedNode(ParsedNode $node): ?Node
    {
        return $this->findOneBy([
            'parser' => $node->getParser(),
            'level' => $node->getLevel(),
            'identifier' => $node->getIdentifier()
        ]);
    }

    /**
     * Found nodes by parent node or node identifiers
     *
     * @param Node|null $parent
     * @param array $identifiers
     * @return Node[]
     */
    public function findByParentAndIdentifiers(Node $parent = null, array $identifiers = [])
    {
        return $this->findBy([
            'parentNode' => $parent,
            'identifier' => $identifiers
        ]);
    }
}
