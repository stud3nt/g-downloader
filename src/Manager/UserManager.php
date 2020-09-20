<?php

namespace App\Manager;

use App\Entity\User;
use App\Manager\Base\EntityManager;
use App\Repository\UsersRepository;
use App\Utils\AppHelper;
use App\Utils\FilesHelper;
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

    public function afterLogin(User $user, string $apiToken): void
    {
        $user->setApiToken($apiToken)
            ->setFileToken(StringHelper::randomStr(32))
            ->setInfoExchangeFile(StringHelper::randomStr(32))
            ->refreshLastLoggedAt();

        $this->createUserCacheFile($user);
        $this->save($user);
    }

    public function createUserCacheFile(User $user): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $fileName = $user->getInfoExchangeFile().'.json';

        $publicCacheFilePath = AppHelper::getPublicDir().'app'.$ds.'assets'.$ds.'json';
        $angularCacheFilepath = AppHelper::getAngularSourceDir().'src'.$ds.'assets'.$ds.'json';

        if (!file_exists($publicCacheFilePath))
            mkdir($publicCacheFilePath);

        if (!file_exists($angularCacheFilepath))
            mkdir($angularCacheFilepath);

        FilesHelper::removeOldFiles($publicCacheFilePath, (60*60));
        FilesHelper::removeOldFiles($angularCacheFilepath, (60*60));

        file_put_contents($publicCacheFilePath.$ds.$fileName, json_encode([]));
        file_put_contents($angularCacheFilepath.$ds.$fileName, json_encode([]));
    }
}
