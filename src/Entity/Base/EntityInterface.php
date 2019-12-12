<?php

namespace App\Entity\Base;

interface EntityInterface extends ConvertableInterface
{
    public function getClass();
    public function getClassName();
    public function getEntity();
}