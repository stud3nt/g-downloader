<?php

namespace App\Factory\Entity;

use App\Converter\EntityConverter;
use App\Entity\Category;
use App\Factory\Base\RequestFactoryInterface;

class CategoryEntityFactory implements RequestFactoryInterface
{
    /**
     * @param $requestData
     * @return Category
     * @throws \ReflectionException
     */
    public function buildFromRequestData($requestData = []): Category
    {
        $category = new Category();

        $modelConverter = new EntityConverter();
        $modelConverter->setData($requestData, $category, true);

        return $category;
    }
}