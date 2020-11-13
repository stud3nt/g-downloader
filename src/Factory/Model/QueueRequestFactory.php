<?php

namespace App\Factory\Model;

use App\Factory\Base\BaseFactory;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedFile;
use App\Model\Request\QueueRequest;

class QueueRequestFactory extends BaseFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): QueueRequest
    {
        return $this->objectSerializer->deserialize($requestData, QueueRequest::class);
    }
}