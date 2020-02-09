<?php

namespace App\Websocket\Base;

use App\Converter\ModelConverter;
use App\Entity\User;
use App\Manager\UserManager;
use App\Model\ParserRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseWebsocket
{
    /** @var ContainerInterface */
    protected $container;

    /** @var UserManager */
    protected $userManager;

    /** @var User|null */
    protected $user = null;

    /** @var ParserRequest|null */
    protected $parserRequest = null;

    /** @var ModelConverter */
    protected $modelConverter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->userManager = $this->container->get(UserManager::class);
        $this->modelConverter = new ModelConverter();
    }

    protected function jsonSuccess($data): string
    {
        return json_encode([
            'status' => 1,
            'data' => $data,
            'error' => null
        ]);
    }

    protected function jsonError($error): string
    {
        return json_encode([
            'status' => -1,
            'data' => null,
            'error' => $error
        ]);
    }

    protected function outputMessage(string $message = null): string
    {
        return '['.(new \DateTime())->format('Y-m-d H:i:s').'] '.$message.PHP_EOL;
    }
}