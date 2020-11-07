<?php

namespace App\Converter;

use App\Converter\Base\BaseConverter;
use App\Utils\DateTimeHelper;

class DateTimeConverter extends BaseConverter
{
    /**
     * @param $value
     * @return string
     */
    public function convertFromObjectValue($value) : string
    {
        return $value->format('Y-m-d H:i:s');
    }

    /**
     * Set value to date
     *
     * @param $value
     * @return \DateTime|mixed
     * @throws \Exception
     */
    public function convertToObjectValue($value) : ?\DateTime
    {
        if (is_numeric($value) || is_int($value)) { // timestamp integer
            $date = new \DateTime();
            return $date->setTimestamp($value);
        } elseif (in_array($value, ['null', null, '---'])) {
            return null;
        } else {
            if (DateTimeHelper::isDateStringValid($value))
                return new \DateTime($value);
            else
                return null;
        }
    }
}