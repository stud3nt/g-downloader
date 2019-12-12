<?php

namespace App\Utils;

class StringHelper
{
    public static $standardKeyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     * @throws \Exception
     */
    public static function randomStr($length, $keyspace = null) : string
    {
        if (!$keyspace) {
            $keyspace = self::$standardKeyspace;
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    /**
     * Checks if variable is an JSON
     *
     * @param $string
     * @return bool
     */
    public static function isJson($string) : bool
    {
        if (is_string($string)) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        return false;
    }

    /**
     * Clears string from unnecessary characters;
     *
     * @param string $string
     * @param bool $clearWhitespaces
     * @param bool $clearNewLines
     * @return string
     */
    public static function clearString(string $string, bool $clearWhitespaces = true, bool $clearNewLines = true) : string
    {
        $convertedString = htmlentities($string, null, 'utf-8');
        $pattenInputs = [];

        if ($clearNewLines) {
            $pattenInputs[] = 's*';
        }

        if ($clearWhitespaces) {
            $pattenInputs[] = '&nbsp;';
        }

        $pattern = '/\\'.implode('|', $pattenInputs).'/m';

        return preg_replace($pattern, '', $convertedString);
    }

    /**
     * Converting 'CamelCase' strings to 'underscore_string';
     *
     * @param $text
     * @param $numbersUnderscore:
     *      - if true, numbers will be underscored (CamelCase2 => camel_case_2)
     *      - if false, numbers wouldn't be underscored (CamelCase2 => camel_case2)
     * @return string
     */
    public static function camelCaseToUnderscore($text, bool $numbersUnderscore = true) : string
    {
        $numbers = $numbersUnderscore ? '0-9' : '';
        $pattern = '/[A-Z'.$numbers.']([A-Z'.$numbers.'](?![a-z]))*/';

        return ltrim(strtolower(preg_replace($pattern, '_$0', $text)), '_');
    }

    /**
     * Converts 'underscore_strings' to camel_case 'CamelCase' type string;
     *
     * @param $text
     * @param bool $firstLetterSmall
     * @return string
     */
    public static function underscoreToCamelCase($text, bool $firstLetterSmall = true) : string
    {
        $textArray = explode('_', $text);
        $camelCaseText = '';

        foreach ($textArray as $key => $segment) {
            $camelCaseText .= ($key == 0 && $firstLetterSmall)
                ? strtolower($segment)
                : ucfirst($segment);
        }

        return $camelCaseText;
    }
}