<?php

namespace App\Converter;

use App\Utils\DateTimeHelper;

class DateTimeConverter extends BaseConverter
{
    /**
     * @param $value
     * @return string
     */
    public function convertFromEntityValue($value) : string
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
    public function convertToEntityValue($value) : ?\DateTime
    {
        if (is_numeric($value) || is_int($value)) { // timestamp integer
            $date = new \DateTime();
            $date->setTimestamp($value);
        } elseif ($value === 'null' || $value === null) {
            $date = null;
        } else {
            if (DateTimeHelper::isDateStringValid($value))
                $date = new \DateTime($value);
            else
                $date = null;
        }

        return $date;

    }
}