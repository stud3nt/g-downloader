<?php

namespace App\Factory\Entity;

use App\Converter\EntityConverter;
use App\Entity\Tag;
use App\Factory\Base\RequestFactoryInterface;

class TagEntityFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return Tag
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): Tag
    {
        $tag = new Tag();

        $modelConverter = new EntityConverter();
        $modelConverter->setData($requestData, $tag, true);

        return $tag;
    }
}