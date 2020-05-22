<?php

namespace App\Tests\Unit\Repository;

use App\Repository\SettingsRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsRepositoryTest extends KernelTestCase
{
    /** @var ContainerInterface */
    protected $containerInstance;

    /** @var SettingsRepository */
    protected $settingsRepository;

    public function setUp(): void
    {
        $this->containerInstance = self::bootKernel()->getContainer();
        $this->settingsRepository = $this->containerInstance->get(SettingsRepository::class);
    }

    public function testGetQueryBuilder()
    {
        $qb = $this->settingsRepository->getQb();
        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }
}