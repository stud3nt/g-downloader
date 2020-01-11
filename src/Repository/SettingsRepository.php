<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function getQb(array $searchParameters = [])
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('s')
            ->from('App\Entity\Setting', 's');

        if ($searchParameters) {
            foreach ($searchParameters as $paramName => $paramValue) {
                if (is_array($paramValue))
                    $qb->andWhere('s.'.$paramName.' IN (:'.$paramName.')')->setParameter($paramName, $paramValue);
                else
                    $qb->andWhere('s.'.$paramName.' = :'.$paramName)->setParameter($paramName, $paramValue);
            }
        }

        return $qb;
    }
}
