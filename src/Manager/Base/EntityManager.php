<?php

namespace App\Manager\Base;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class EntityManager
{
    protected $entityName;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $paginator;

    protected $repository;
    protected $namespace;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(sprintf('App:%s%s', $this->namespace, $this->entityName));
    }

    public function getBy(Array $criteria = [], $orderBy = ['id' => 'desc'])
    {
        return $this->repository->findBy($criteria, $orderBy);
    }

    public function get($id)
    {
        return $this->repository->find($id);
    }

    public function getOneBy(Array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function getRandom(array $criteria = [])
    {
        $qb = $this->repository->createQueryBuilder('r')
            ->select('r')
            ->addSelect('RAND() as HIDDEN ord');

        if ($criteria) {
            foreach ($criteria as $cKey => $cValue) {
                $qb->andWhere('r.'.$cKey.' = :kk_'.$cKey)->setParameter('kk_'.$cKey, $cValue);
            }
        }

        return $qb->orderBy('ord', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCount()
    {
        return $this->repository->count([]);
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
