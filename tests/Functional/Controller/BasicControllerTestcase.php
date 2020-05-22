<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class BasicControllerTestcase extends WebTestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $containerInstance;

    /** @var RouterInterface */
    protected $router;

    /** @var KernelBrowser */
    protected $client;

    public function setUp()
    {
        $this->containerInstance = self::bootKernel()->getContainer();
        $this->router = $this->containerInstance->get('router');
        $this->client = $this->containerInstance->get('test.client');
    }
}