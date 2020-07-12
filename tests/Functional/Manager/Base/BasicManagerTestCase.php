<?php

namespace App\Tests\Functional\Manager\Base;

use App\Converter\ModelConverter;
use App\Manager\Base\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

abstract class BasicManagerTestCase extends WebTestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $containerInstance;

    /** @var RouterInterface */
    protected $router;

    /** @var KernelBrowser */
    protected $client;

    /** @var EntityManager */
    protected $manager;

    public function setUp()
    {
        $this->containerInstance = self::bootKernel()->getContainer();
    }

    public function testGetCount()
    {
        $count = $this->manager->getCount();

        $this->assertIsInt($count);
    }

    public function testGetRandom()
    {
        $count = $this->manager->getCount();
        $random = $this->manager->getRandom();

        if ($count > 0) {
            $this->assertNotEmpty($random);
            $this->assertIsObject($random);
            $this->assertTrue(
                method_exists($random, 'getId'),
                'Method "getId()" does not exists in class '.get_class($this->manager)
            );
        } else {
            $this->assertEmpty($random);
        }
    }

    public function loadManager(string $managerName): ?EntityManager
    {
        return $this->containerInstance->get($managerName);
    }

    public function loadRepository(string $repositoryName): ?ServiceEntityRepository
    {
        return $this->containerInstance->get($repositoryName);
    }

    public function getModelConverter(): ModelConverter
    {
        return new ModelConverter();
    }
}