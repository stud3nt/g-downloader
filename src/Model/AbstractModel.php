<?php

namespace App\Model;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Base\AbstractEntity;

abstract class AbstractModel
{
    /**
     * @param AbstractEntity|null $entity
     * @throws \ReflectionException
     * @return $this;
     */
    public function setFromEntity(AbstractEntity $entity = null): self
    {
        if (!$entity)
            return $this;

        $entityConverter = new EntityConverter();
        $modelConverter = new ModelConverter();

        $entityData = $entityConverter->convert($entity);

        $modelConverter->setData($entityData, $this);

        return $this;
    }
}