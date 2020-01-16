<?php

namespace App\Repository;

use App\Entity\Parser\Node;
use App\Enum\NodeLevel;
use App\Model\ParsedNode;
use App\Model\ParserRequest;
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

    public function findOneByParsedNode(ParsedNode $node)
    {
        return $this->findOneBy([
            'parser' => $node->getParser(),
            'level' => $node->getLevel(),
            'identifier' => $node->getIdentifier()
        ]);
    }

    public function findSavedNodesByRequestAndIdentifiers(ParserRequest $parserRequest, array $identifiers = [])
    {
        return $this->findBy([
            'identifier' => $identifiers,
            'parser' => $parserRequest->currentNode->parser,
            'level' => NodeLevel::determineNextLevel($parserRequest->currentNode->level)
        ]);
    }
}
