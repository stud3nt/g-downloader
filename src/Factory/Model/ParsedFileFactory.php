<?php

namespace App\Factory\Model;

use App\Factory\Base\BaseFactory;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedFile;

class ParsedFileFactory extends BaseFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): ParsedFile
    {
        return $this->objectSerializer->deserialize($requestData, ParsedFile::class);
    }
}