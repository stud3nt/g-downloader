<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $usernameEmail
     * @return User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByUsernameOrEmail(string $usernameEmail): ?User
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('App:User', 'u')
            ->where('u.username = :usernameEmail')
            ->orWhere('u.email = :usernameEmail')
            ->setParameter('usernameEmail', $usernameEmail)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
