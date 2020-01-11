<?php

namespace App\Repository;

use App\Entity\Parser\NodeDataStorage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NodeDataStorage|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodeDataStorage|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodeDataStorage[] findAll()
 * @method NodeDataStorage[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeDataStorageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeDataStorage::class);
    }
}
