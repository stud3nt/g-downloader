<?php

namespace App\Factory\Base;

interface RequestFactoryInterface
{
    public function buildFromRequestData(array $requestData = []);
}