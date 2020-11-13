<?php

namespace App\Factory;

use App\Factory\Base\BaseFactory;

class ClassByRequestFactory extends BaseFactory
{
    /**
     * @param array $requestData
     * @param string $className
     * @return array|mixed
     * @throws \ReflectionException
     * @throws \Symfony\Component\VarExporter\Exception\ClassNotFoundException
     */
    public function buildClassFromRequestData(array $requestData, string $className)
    {
        return $this->objectSerializer->deserialize($requestData, $className);
    }
}