<?php

namespace App\Parser\Base;

use App\Model\ParsedFile;
use App\Model\ParserRequestModel;

/**
 * Interface ParserInterface
 *
 * For parser classes;
 *
 * @package App\Parser\Base
 */
interface ParserInterface
{
    public function getBoardsListData(ParserRequestModel &$parserRequestModel) : ParserRequestModel;

    public function getBoardData(ParserRequestModel &$parserRequestModel) : ParserRequestModel;

    public function getGalleryData(ParserRequestModel &$parserRequestModel) : ParserRequestModel;

    public function getFileData(ParsedFile &$parsedFile) : ParsedFile;

    public function getFilePreview(ParsedFile &$parsedFile) : ParsedFile;
}