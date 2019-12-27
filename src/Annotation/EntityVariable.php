<?php

namespace App\Annotation;

/**
 * @Annotation
 */
final class EntityVariable
{
    // converting model names
    public $convertNames = [];

    // if true, variable will be converted in all model names.
    public $inAllConvertNames = true;

    // if true, property should be converted for properly display
    public $convertable = false;

    // property can be read from entity;
    public $writable = false;

    // property can be saved to entity;
    public $readable = false;

    // field type;
    public $type = null;

    // max field length (null === unlimited)
    public $length = null;

    // field converter
    public $converter = null;

    // converter settings
    public $converterOptions = [];
}