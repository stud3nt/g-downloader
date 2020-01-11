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

    public function getCount()
    {
        return $this->repository->getCount();
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
