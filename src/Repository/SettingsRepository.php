<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function getParserSettingsQb(string $parserName)
    {
        return $this->_em->createQueryBuilder()
            ->select('s')
            ->from('App\Entity\Setting', 's')
            ->where('s.group = :group')
            ->setParameter('group', $parserName);
    }

    public function getQb(array $searchParameters = [])
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('s')
            ->from('App\Entity\Setting', 's');

        if ($searchParameters) {
            foreach ($searchParameters as $paramName => $paramValue) {
                $qb->andWhere('s.'.$paramName.' = :'.$paramName)
                    ->setParameter($paramName, $paramValue);
            }
        }

        return $qb;
    }
}
