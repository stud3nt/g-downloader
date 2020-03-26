<?php

namespace App\Utils;

class DateTimeHelper
{
    public static function dateDifference(\DateTime $date, \DateTime $currentDate = null): string
    {
        if (!$currentDate)
            $currentDate = new \DateTime();

        $difference = $currentDate->diff($date);
        $differenceText = '';

        if ($difference->y > 0)
            $differenceText .= (empty($text) ? '' : ' ').$difference->y.' year'.(($difference->y > 1) ? 's' : '');

        if ($difference->m > 0) {
            $differenceText .= (empty($text) ? '' : ' ').$difference->m.' month'.(($difference->m > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->d > 0) {
            $differenceText .= (empty($text) ? '' : ' ').$difference->d.' day'.(($difference->d > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->h > 0) {
            $differenceText .= (empty($text) ? '' : ' ').$difference->h.' hour'.(($difference->h > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->m > 0) {
            $differenceText .= (empty($text) ? '' : ' ').$difference->m.' minute'.(($difference->m > 1) ? 's' : '');

            if ($difference->m > 2)
                return $differenceText;
        }

        if ($difference->s > 0)
            $differenceText .= (empty($text) ? '' : ' ').$difference->s.' second'.(($difference->s > 1) ? 's' : '');

        return $differenceText;
    }
}