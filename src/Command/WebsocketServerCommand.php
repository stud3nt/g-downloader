<?php

namespace App\Command;

use App\Websocket\StatusWebsocket;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebsocketServerCommand extends Command
{
    protected static $defaultName = 'app:websocket:server';

    /** @var ContainerInterface */
    private $container;

    public function __construct(string $name = null, ContainerInterface $container = null)
    {
        $this->container = $container;

        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parserWebsocket = (new StatusWebsocket($this->container));

        $server = IoServer::factory(
            new HttpServer(
                (new WsServer($parserWebsocket))
            ), 2002
        );

        $server->run();
    }
}