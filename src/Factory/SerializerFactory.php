<?php

namespace App\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    /**
     * Creates basic entity normalizer (entity to array)
     *
     * @param array $defaultContext = [];
     * @return Serializer
     */
    public function getEntityNormalizer(array $defaultContext = []): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(
            new AnnotationLoader(
                new AnnotationReader()
            )
        );

        return new Serializer([
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                null,
                null,
                null,
                null,
                null,
                $defaultContext
            )
        ]);
    }
}