<?php

namespace App\Annotation;

/**
 * @Annotation
 */
final class ModelVariable
{
    // field type;
    public $type = null;

    // field converter
    public $converter = null;

    // converter settings
    public $converterOptions = [];
}