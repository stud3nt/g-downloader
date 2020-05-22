<?php

namespace App\Repository;

use App\Entity\Parser\NodeSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method NodeSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodeSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodeSettings[]    findAll()
 * @method NodeSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeSettings::class);
    }
}
