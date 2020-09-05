<?php

namespace App\Websocket;

use App\Entity\User;
use App\Enum\WebsocketOperation;
use App\Factory\ParsedFileFactory;
use App\Factory\ParserRequestFactory;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Model\Status;
use App\Websocket\Base\BaseWebsocket;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class StatusWebsocket extends BaseWebsocket implements MessageComponentInterface
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
        /** @var User|null $user */
        $msg = json_decode($message);
        $user = $this->userManager->getOneBy([
            'apiToken' => ($msg->_token ?? null)
        ]);

        if ($user) {
            $response = null;

            try {
                switch ($msg->_operation) {
                    case WebsocketOperation::ParserProgress:
                        $parserRequest = (new ParserRequestFactory())->buildFromRequestData($msg->_data);
                        $status = $parserRequest->getStatus()->read();

                        if (!$status)
                            $status = $this->modelConverter->convert((new Status()));

                        $response = $status;
                        break;

                    case WebsocketOperation::DownloadListStatus:
                        $response = $this->container->get(DownloadManager::class)->getStatusData($user);
                        break;

                    case WebsocketOperation::DownloadFileStatus:
                        $parsedFile = (new ParsedFileFactory())->buildFromRequestData($msg->_data);
                        $status = $this->container->get(FileManager::class)->getFileDownloadStatus($parsedFile);
                        $response = $this->modelConverter->convert($status);
                        break;

                    default:
                        $connection->send(
                            $this->jsonError('WRONG PARSER REQUEST.')
                        );
                        break;
                }
            } catch (\Throwable $e) {
                $this->jsonError([
                    'errorCode' => $e->getCode(),
                    'errorMessage' => $e->getMessage(),
                    'errorLine' => $e->getLine(),
                    'errorTrace' => $e->getTraceAsString()
                ]);
            }

            if ($response) {
                $connection->send(
                    $this->jsonSuccess($response)
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