<?php

namespace App\Utils;

use App\Enum\FileSpecification;
use App\Enum\FileType;
use Symfony\Component\HttpFoundation\File\File;

class FilesHelper
{
    protected static $fileSizesUnits = ["Bytes", "KB", "MB", "GB", "TB", "PB"];

    protected static $extendedFilesSizeUnits = [
        [
            'names' => [
                'B', 'Bytes'
            ]
        ],
        [
            'names' => [
                'KB', 'KiloBytes'
            ]
        ],
        [
            'names' => [
                'MB', 'MegaBytes'
            ]
        ],
        [
            'names' => [
                'GB', 'GigaBytes'
            ]
        ],
        [
            'names' => [
                'TB', 'TeraBytes'
            ]
        ],
        [
            'names' => [
                'PB', 'PetaBytes'
            ]
        ],
    ];

    protected static $imageMimeHexTypes = [
        'image/jpeg' => 'FFD8',
        'image/png' => '89504E470D0A1A0A',
        'image/gif' => '474946',
        'image/bmp' => '424D'
    ];

    /**
     * Gets file name from string
     *
     * @param string $fileString
     * @return string
     */
    public static function getFileName(string $fileString, bool $withExtension = false) : string
    {
        return (pathinfo($fileString)['filename'] ?? '').(($withExtension) ? '.'.self::getFileExtension($fileString) : '');
    }

    /**
     * Gets file extension from string
     *
     * @param string $fileString
     * @return mixed
     */
    public static function getFileExtension(string $fileString) : string
    {
        $pathinfo = pathinfo($fileString);

        if ($extension = $pathinfo['extension']) {
            if ($queryStart = strpos($extension, '?'))
                $extension = substr($extension, 0, $queryStart);

            return strtolower($extension);
        }

        return '';
    }

    /**
     * Gets file mime type from string (based on extension) or from file (if exists and available)
     *
     * @param string $fileString
     * @param bool $fromString
     * @return string
     */
    public static function getFileMimeType(string $fileString, bool $fromString = false) : string
    {
        if ($fromString || substr($fileString, 0, 4) === 'http') { // force string analysis or web URL
            $fileExtension = self::getFileExtension($fileString);

            foreach (FileSpecification::getData() as $fileData) {
                if ($fileExtension == $fileData['extension']) {
                    return $fileData['mimeType'];
                }
            }
        } elseif (file_exists($fileString)) {
            return (new File($fileString))->getMimeType();
        }

        return '';
    }

    /**
     * Determines file type
     *
     * @param string $fileString
     * @param bool $fromString
     * @return string
     */
    public static function getFileType(string $fileString, bool $fromString = false) : string
    {
        $mimeType = self::getFileMimeType($fileString, $fromString);

        if (!$mimeType) {
            return '';
        }

        return ($mimeType)
            ? (array_key_exists($mimeType, self::$imageMimeHexTypes))
                ? FileType::Image
                : FileType::Video
            : '';
    }

    /**
     * Converts numeric bytes to text size
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function bytesToSize(int $bytes = null, $precision = 2) : string
    {
        if ($bytes > 0) {
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count(self::$fileSizesUnits) - 1);

            $bytes /= (1 << (10 * $pow));

            return round($bytes, $precision) . ' ' . self::$fileSizesUnits[$pow];
        }

        return "0 B";
    }

    /**
     * Converts text size to bytes integer
     *
     * @param string|null $size
     * @return int
     */
    public static function sizeToBytes(string $size = null) : int
    {
        $tmp = str_split($size);
        $sizeValue = '';
        $sizeSign = null;

        foreach ($tmp as $letter) {
            if (preg_match('/[0-9\,\.]{1}/', $letter)) {
                $sizeValue .= $letter;
            } elseif (preg_match('/[a-zA-Z]{1}/', $letter)) {
                $sizeSign .= $letter;
            }
        }

        if (empty($sizeSign)) {
            return (int)$sizeValue;
        }

        $sizeValue = str_replace(',', '.', $sizeValue);

        foreach (self::$extendedFilesSizeUnits as $sizeKey => $sizeConfig) {
            $names = $sizeConfig['names'];

            foreach ($names as $name) {
                if (strtoupper($sizeSign) == strtoupper($name)) {
                    if (($sizeKey) > 0) {
                        return (pow(1024, $sizeKey) * (float)$sizeValue);
                    }

                    break;
                }
            }
        }

        return (int)$sizeValue;
    }

    /**
     * Clears folder string from forbidden characters
     *
     * @param string|null $rawString
     * @param boolean $allowSlashes
     * @param int $maxLength - maximum folder name length
     * @return string|null
     */
    public static function fileNameFromString(?string $rawString, bool $allowSlashes = false, int $maxLength = 80): ?string
    {
        $rawString = str_replace( // replacing ABSOLUTELY forbidden characters;
            ['*', '?', '<', '>', '|', '”', '&#039;', '&quot;', '’'],
            ['_', 'q', '[', ']', '-', '\'\'', '\'\'', '\'\'', '\'\''],
            $rawString
        );

        $rawString = preg_replace('/[^(\x20-\x7F)]*/', '', $rawString);
        $rawString = preg_replace('!\s+\s+!', ' - ', $rawString);

        if ($allowSlashes)
            $pattern = '/[^a-zA-Z0-9\/\.\,\-\_\(\)\;\]\[\'\&\%\#\@\!\s+]/';
        else
            $pattern = '/[^a-zA-Z0-9\-\.\,\-\_\(\)\;\]\[\'\&\%\#\@\!\s+]/';

        $string = preg_replace($pattern, '-', $rawString);

        return trim(substr($string, 0, $maxLength));
    }

    public static function folderNameFromString(string $rawString): string
    {
        return self::fileNameFromString($rawString, true);
    }

    public static function removeOldFiles(string $catalog, int $minTimestampDifference = 60, array $filters = []): void
    {
        foreach (scandir($catalog) as $file) {
            if (!in_array($file, ['.', '..'])) {
                if (filemtime($catalog.DIRECTORY_SEPARATOR.$file) < (time() - $minTimestampDifference)) {
                    unlink($catalog.DIRECTORY_SEPARATOR.$file);
                }
            }
        }
    }
}