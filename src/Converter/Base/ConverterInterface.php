<?php

namespace App\Converter\Base;

interface ConverterInterface
{
    public function toValue();

    public function fromValue();
}