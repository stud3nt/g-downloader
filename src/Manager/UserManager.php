<?php

namespace App\Manager;

use App\Entity\User;
use App\Manager\Base\EntityManager;
use App\Repository\UsersRepository;
use App\Utils\StringHelper;

class UserManager extends EntityManager
{
    protected $entityName = 'User';

    /** @var UsersRepository */
    protected $repository;

    /**
     * @param string $usernameOrEmail
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        return $this->repository->findOneByUsernameOrEmail($usernameOrEmail);
    }

    public function afterLogin(User $user, string $apiToken)
    {
        $user->setApiToken($apiToken)
            ->setFileToken(StringHelper::randomStr(32))
            ->setCacheToken(StringHelper::randomStr(32))
            ->refreshLastLoggedAt();

        $this->save($user);
    }
}
