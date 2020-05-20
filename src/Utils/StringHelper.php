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
    public static function clearString(string $string, bool $clearWhitespaces = true, bool $clearNewLines = true): ?string
    {
        $convertedString = htmlentities($string, null, 'utf-8');
        $htmlEntitiesPattern = '/[\&]{1}[a-zA-Z]{2,7}[\;]{1}/';

        if (preg_match($htmlEntitiesPattern, $convertedString))
            $convertedString = preg_replace($htmlEntitiesPattern, '-', $convertedString);

        $convertedString = preg_replace('/[^(\x20-\x7F)]*/', '', $convertedString);
        $convertedString = trim(preg_replace('/\s+!/', ' ', $convertedString));

        return (string)trim($convertedString);
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
        $text = mb_strtolower($text);
        $textArray = explode('_', $text);
        $camelCaseText = '';

        foreach ($textArray as $key => $segment) {
            $camelCaseText .= ($key == 0 && $firstLetterSmall)
                ? strtolower($segment)
                : ucfirst($segment);
        }

        return $camelCaseText;
    }

    /**
     * Clears string and replace '-' to all characters excepting 'a-z', '0-9' and '-' chars.
     *
     * @param string $inputString
     * @return string
     */
    public static function basicCharactersOnly(string $inputString): string
    {
        return preg_replace('/[^a-zA-Z0-9\-]/', '-', mb_strtolower($inputString));
    }
}