<?php

namespace App\Websocket;

use App\Websocket\Base\BaseWebsocket;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ParserWebsocket extends BaseWebsocket implements MessageComponentInterface
{
    /** @var ConnectionInterface */
    public $connection;

    /**
     * New connection
     *
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        $this->connections = $connection;

        $connection->send(json_encode("CONNECTION OPEN"));

        echo $this->outputMessage('CONNECTION OPEN');
    }

    /**
     * Handle sent data;
     *
     * @param ConnectionInterface $connection
     * @param string $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $connection, $message): void
    {
        $this->decodeMessage($message);

        if ($this->user) {
            if ($this->parserRequest) {
                $status = $this->parserRequest->getStatus()->read();

                if ($status)
                    $connection->send(
                        $this->jsonSuccess(
                            $status
                        )
                    );
            } else {
                $connection->send(
                    $this->jsonError('WRONG PARSER REQUEST.')
                );
            }
        } else {
            $connection->send(
                $this->jsonError('BAD CREDENTIALS.')
            );
        }

        echo $this->outputMessage('DATA RECEIVED, RESPONSE SENT');
    }

    public function onError(ConnectionInterface $connection, \Exception $exception): void
    {
        $connection->send("ERROR OCCURED");

        echo $this->outputMessage('AN ERROR OCCURED');
        echo $this->outputMessage($exception->getMessage());
    }

    public function onClose(ConnectionInterface $connection): void
    {
        $connection->send('CONNECTION CLOSED');

        $this->connection = null;

        echo $this->outputMessage('CONNECTION CLOSED');
    }
}