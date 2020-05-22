<?php

namespace App\Tests\Functional\Controller;

use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    protected function loginUserIntoClient(string $username, KernelBrowser &$client): KernelBrowser
    {
        $session = $this->containerInstance->get('session');
        $user = $this->containerInstance->get(UserManager::class)->getByUsernameOrEmail($username);

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    protected function logoutUserFromClient(KernelBrowser &$client): KernelBrowser
    {
        $client->getCookieJar()->clear();

        return $client;
    }
}