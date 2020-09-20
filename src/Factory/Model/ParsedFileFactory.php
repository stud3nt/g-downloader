<?php

namespace App\Factory\Model;

use App\Converter\ModelConverter;
use App\Factory\Base\RequestFactoryInterface;
use App\Model\ParsedFile;

class ParsedFileFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return ParsedFile
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): ParsedFile
    {
        $parserFile = new ParsedFile();

        $modelConverter = new ModelConverter();
        $modelConverter->setData($requestData, $parserFile, true);

        return $parserFile;
    }
}