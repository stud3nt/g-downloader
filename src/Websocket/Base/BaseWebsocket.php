<?php

namespace App\Websocket\Base;

use App\Converter\ModelConverter;
use App\Entity\User;
use App\Factory\ParserRequestFactory;
use App\Factory\RedisFactory;
use App\Manager\UserManager;
use App\Model\ParserRequest;
use React\EventLoop\LoopInterface;
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

    /** @var \stdClass */
    protected $message = null;

    /** @var ModelConverter */
    protected $modelConverter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->userManager = $this->container->get(UserManager::class);
        $this->modelConverter = new ModelConverter();
    }

    /**
     * Decodes json message and authorizes user (if valid token is sent);
     *
     * @param string|null $message
     * @throws \ReflectionException
     */
    protected function decodeMessage(string $message = null): void
    {
        $this->message = json_decode($message);

        $this->user = $this->userManager->getOneBy([
            'apiToken' => ($this->message->_token ?? null)
        ]);

        if (property_exists($this->message, 'parserRequest')) {
            $this->parserRequest = (new ParserRequestFactory())->buildFromRequestData(
                json_decode(
                    json_encode($this->message->parserRequest), true
                )
            );
        }
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