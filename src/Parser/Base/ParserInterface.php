<?php

namespace App\Parser\Base;

use App\Entity\Parser\File;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use Ratchet\ConnectionInterface;

/**
 * Interface ParserInterface
 *
 * For parser classes;
 *
 * @package App\Parser\Base
 */
interface ParserInterface
{
    public function getOwnersList(ParserRequest &$parserRequest): ParserRequest;

    public function getBoardsListData(ParserRequest &$parserRequest): ParserRequest;

    public function getBoardData(ParserRequest &$parserRequest): ParserRequest;

    public function getGalleryData(ParserRequest &$parserRequest): ParserRequest;

    public function getFileData(ParsedFile &$parsedFile): ParsedFile;

    public function getFilePreview(ParsedFile &$parsedFile): ParsedFile;

    public function generateFileCurlRequest(File &$file): File;

    public function determineFileSubfolder(File $file): ?string;

    public function clearParserCache(ParserRequest $parserRequest): void;
}