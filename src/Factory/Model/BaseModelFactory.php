<?php

namespace App\Factory\Model;

use App\Factory\SerializerFactory;

class BaseModelFactory
{
    protected $serializer;

    public function __construct()
    {
        $this->serializer = SerializerFactory::getEntityNormalizer();
    }
}