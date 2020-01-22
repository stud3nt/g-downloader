<?php

namespace App\Factory;

use App\Converter\ModelConverter;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedFile;

class ParsedFileFactory implements RequestFactoryInterface
{
    /**
     * @param array $requestData
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function buildFromRequestData(array $requestData = []): ParsedFile
    {
        $parserFile = new ParsedFile();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestData, $parserFile);

        return $parserFile;
    }
}