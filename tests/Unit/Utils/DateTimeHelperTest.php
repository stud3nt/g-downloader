<?php

namespace App\Tests\Unit\Utils;

use App\Utils\DateTimeHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateTimeHelperTest extends KernelTestCase
{
    private $dateTimeDifferenceCases = [
        [
            'date1' => '2015-01-01',
            'date2' => '2016-02-01',
            'expectedText' => '1 year 1 month'
        ],
        [
            'date1' => '2016-01-01',
            'date2' => '2016-02-01',
            'expectedText' => '1 month'
        ],
        [
            'date1' => '2016-01-01',
            'date2' => '2016-02-02',
            'expectedText' => '1 month 1 day'
        ],
        [
            'date1' => '2015-01-01 15:32:00',
            'date2' => '2015-01-01 16:00:00',
            'expectedText' => '28 minutes'
        ],
        [
            'date1' => '2015-01-01 15:32:45',
            'date2' => '2015-01-01 16:00:00',
            'expectedText' => '27 minutes'
        ]
    ];

    private $dateTimeStringCases = [
        '2013-13-01' => false,
        '2013-2-25' => true,
        '2013-12-01 12:32:33' => true,
        '15-12-01' => true,
        '1970-12-01' => true,
        'siemanko' => false
    ];

    public function testDateTimeDifference()
    {
        foreach ($this->dateTimeDifferenceCases as $datePackage) {
            $date1 = new \DateTime($datePackage['date1']);
            $date2 = new \DateTime($datePackage['date2']);
            $diffText = DateTimeHelper::dateDifference($date1, $date2);

            $this->assertEquals($datePackage['expectedText'], $diffText);
        }
    }

    public function testDateTimeStringValid()
    {
        foreach ($this->dateTimeStringCases as $dateString => $expectedResult) {
            $result = DateTimeHelper::isDateStringValid($dateString);

            if ($expectedResult === false)
                $this->assertFalse($result);
            else
                $this->assertTrue($result);
        }
    }
}