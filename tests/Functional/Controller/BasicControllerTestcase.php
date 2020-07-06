<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Manager\Object\NodeManager;
use App\Manager\UserManager;
use App\Utils\StringHelper;
use App\Utils\TestsHelper;
use Doctrine\ORM\EntityManager;
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

    protected function executeAnonymousUserRequest(
        KernelBrowser $client,
        string $route,
        string $method = 'GET',
        bool $expectLoginRedirection = true
    ): ?Response
    {
        $this->logoutUserFromClient($client);

        // execute request as anonymous user;
        $this->client->request($method, $this->router->generate($route));

        return $this->clientResponseAssertions($client, $expectLoginRedirection);
    }

    protected function executeAdminUserRequest(
        KernelBrowser $client,
        string $route,
        string $method = 'GET',
        bool $expectLoginRedirection = false,
        array $requestData = []
    ): ?Response
    {
        $this->loginUserIntoClient(TestsHelper::$testAdminUser['username'], $client);

        // execute request as logged admin user
        $this->client->request($method, $this->router->generate($route), $requestData);

        return $this->clientResponseAssertions($client, $expectLoginRedirection);
    }

    protected function clientResponseAssertions(KernelBrowser $client, bool $expectLoginRedirection = false): ?Response
    {
        $response = $client->getResponse();
        $url = $client->getRequest()->getRequestUri();

        if ($expectLoginRedirection) {
            $this->assertFalse($response->isSuccessful(), 'URL: '.$url.' is not successfull :(');
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertContains('login', $response->headers->get('location'));
        } else {
            $this->assertTrue($response->isSuccessful(), 'URL: '.$url.' is not successfull :(');
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertIsString($response->getContent());
        }

        return $response;
    }

    protected function responseToObjectAssertions(Response $response): \stdClass
    {
        $this->assertTrue(StringHelper::isJson($response->getContent()));

        return json_decode($response->getContent());
    }

    protected function loginUserIntoClient(string $username, KernelBrowser $client): KernelBrowser
    {
        $session = $this->containerInstance->get('session');
        $user = $this->loadUserByUsername($username);

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

    protected function loadUserByUsername(string $username): ?User
    {
        return $this->containerInstance->get(UserManager::class)->getByUsernameOrEmail($username);
    }

    protected function loadEntityManager(): EntityManager
    {
        return $this->containerInstance->get('doctrine.orm.entity_manager');
    }

    protected function loadNodeManager(): NodeManager
    {
        return $this->containerInstance->get(NodeManager::class);
    }
}