<?php

namespace App\Tests\Functional\Controller;

use App\Manager\UserManager;
use App\Utils\TestsHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;

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

    protected function executeAnonymousUserRequest(KernelBrowser &$client, string $route, string $method = 'GET', bool $expectLoginRedirection = true): ?Response
    {
        $this->logoutUserFromClient($client);

        // execute request as anonymous user;
        $this->client->request($method, $this->router->generate($route));

        $response = $this->client->getResponse();

        $this->responseAssertions($response, $expectLoginRedirection);

        return $response;
    }

    protected function executeAdminUserRequest(KernelBrowser &$client, string $route, string $method = 'GET', bool $expectLoginRedirection = false): ?Response
    {
        $this->loginUserIntoClient(TestsHelper::$testAdminUser['username'], $client);

        // execute request as logged admin user
        $this->client->request($method, $this->router->generate($route));

        $response = $this->client->getResponse();

        $this->responseAssertions($response, $expectLoginRedirection);

        return $this->client->getResponse();
    }

    protected function responseAssertions(Response $response, bool $expectLoginRedirection = false)
    {
        if ($expectLoginRedirection) {
            $this->assertFalse($response->isSuccessful());
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertContains('login', $response->headers->get('location'));
        } else {
            $this->assertTrue($response->isSuccessful());
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertIsString($response->getContent());
        }
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