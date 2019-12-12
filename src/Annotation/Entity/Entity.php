<?php

namespace App\Annotation\Entity;

/**
 * @Annotation
 */
final class Entity
{
    // entity is convertable;
    public $convertable = false;

    // can create new entity
    public $allowAdd = false;

    // can delete entity
    public $allowDelete = false;
}