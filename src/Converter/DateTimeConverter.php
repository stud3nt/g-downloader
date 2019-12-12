<?php

namespace App\Converter;

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
        if (is_numeric($value)) { // timestamp integer
            $date = new \DateTime();
            $date->setTimestamp($value);
        } elseif ($value === 'null') {
            $date = null;
        } else {
            $date = new \DateTime($value);
        }

        return $date;

    }
}