<?php

namespace App\Manager\Base;

use App\Model\ParserRequest;

interface ParserObjectManagerInterface
{
    public function addToDatabase(ParserRequest &$parserRequest) : ParserRequest;

    public function removeFromDatabase(ParserRequest &$parserRequest) : ParserRequest;
}