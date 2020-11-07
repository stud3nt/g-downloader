<?php

namespace App\Model;

use App\Converter\ObjectSerializer;
use App\Entity\Base\AbstractEntity;

abstract class AbstractModel
{
    /**
     * @param AbstractEntity|null $entity
     * @throws \ReflectionException
     * @return $this;
     */
    public function setFromEntity(AbstractEntity $entity, string $className): self
    {
        $objectSerializer = new ObjectSerializer();

        $entityData = $objectSerializer->serialize($entity);

        $objectSerializer->deserializeObject($entityData, $className);

        return $this;
    }
}