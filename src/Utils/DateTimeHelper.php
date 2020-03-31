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
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->y.' year'.(($difference->y > 1) ? 's' : '');

        if ($difference->m > 0) {
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->m.' month'.(($difference->m > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->d > 0) {
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->d.' day'.(($difference->d > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->h > 0) {
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->h.' hour'.(($difference->h > 1) ? 's' : '');
            return $differenceText;
        }

        if ($difference->i > 0) {
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->i.' minute'.(($difference->i > 1) ? 's' : '');

            if ($difference->i > 2)
                return $differenceText;
        }

        if ($difference->s > 0)
            $differenceText .= (empty($differenceText) ? '' : ' ').$difference->s.' second'.(($difference->s > 1) ? 's' : '');

        return $differenceText;
    }
}