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

    // /**
    //  * @return NodeSettings[] Returns an array of NodeSettings objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NodeSettings
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
