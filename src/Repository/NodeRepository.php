<?php

namespace App\Repository;

use App\Entity\Parser\Node;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Node|null find($id, $lockMode = null, $lockVersion = null)
 * @method Node|null findOneBy(array $criteria, array $orderBy = null)
 * @method Node[] findAll()
 * @method Node[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Node::class);
    }
}
