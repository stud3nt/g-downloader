<?php

namespace App\Manager\Base;

use App\Model\ParserRequestModel;

interface ParserObjectManagerInterface
{
    public function addToDatabase(ParserRequestModel &$parserRequestModel) : ParserRequestModel;

    public function removeFromDatabase(ParserRequestModel &$parserRequestModel) : ParserRequestModel;
}