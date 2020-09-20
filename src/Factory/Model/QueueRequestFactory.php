<?php

namespace App\Factory\Model;

use App\Enum\NormalizationGroup;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedFile;
use App\Model\Request\QueueRequest;

class QueueRequestFactory extends BaseModelFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): QueueRequest
    {
        /** @var QueueRequest $queueRequest */
        $queueRequest = $this->serializer->denormalize($requestData, QueueRequest::class, 'json', [
            'groups' => NormalizationGroup::QueuedFile
        ]);

        return $queueRequest;
    }
}