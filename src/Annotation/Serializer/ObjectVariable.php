<?php

namespace App\Annotation\Serializer;

/**
 * @Annotation
 */
final class ObjectVariable
{
    const TYPE_ARRAY = 'array';
    const TYPE_JSON = 'json';
    const TYPE_INT = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_ITERABLE = 'iterable';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STDCLASS = 'stdClass';

    // convertable group name. If empty - all;
    public array $group = [];

    // property can be null. Default true.
    public bool $nullable = true;

    // property can be writed. Default true.
    public bool $writable = true;

    // field type. If null - automatic detection
    public ?string $type = null;

    // property class name (if array of classes - $this->writable must be true);
    public ?string $class = null;

    // variable converter class name
    public ?string $converter = null;

    // variable converter settings
    public ?array $converterOptions = [];
}