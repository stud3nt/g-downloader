<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UsersRepository;
use App\Utils\TestsHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UsersRepositoryTest extends KernelTestCase
{
    /** @var ContainerInterface */
    protected $containerInstance;

    /** @var UsersRepository */
    protected $usersRepository;

    public function setUp(): void
    {
        $this->containerInstance = self::bootKernel()->getContainer();
        $this->usersRepository = $this->containerInstance->get(UsersRepository::class);
    }

    public function testGetFindOneByUsernameOrEmail()
    {
        $user = $this->usersRepository->findOneByUsernameOrEmail(TestsHelper::$testUsername);

        $this->assertNotEmpty($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue(($user->getUsername() === TestsHelper::$testUsername || $user->getEmail() === TestsHelper::$testUsername));
    }
}