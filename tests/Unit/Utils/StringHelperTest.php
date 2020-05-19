<?php

namespace App\Tests\Unit\Utils;

use App\Utils\StringHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StringHelperTest extends KernelTestCase
{
    private $namesTestCases = [
        '5\'8" 155, 37 years old' => '5\'8\'\' 155, 37 years old',
        'my first drop ♥️ 18' => 'my first drop - 18',
        'Eva & Katya' => 'Eva & Katya',
        '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback 😊' => '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback'
    ];

    private $clearStringTestCases = [

    ];

    public function testRandomString()
    {
        for ($i=1; $i<=10; $i++) {
            $stringLength = (10 + ($i*2));
            $string = StringHelper::randomStr($stringLength);

            $this->assertIsString($string);
            $this->assertEquals($stringLength, strlen($string));
        }
    }

    public function testIsJson()
    {
        $jsonString = '{"menu":{"id":"file","value":"File","popup":{"menuitem":[{"value":"New","onclick":"CreateNewDoc()"},
            {"value":"Open","onclick":"OpenDoc()"},{"value":"Close","onclick":"CloseDoc()"}]}}}';
        $nonJsonString = StringHelper::randomStr(100);

        $this->assertTrue(StringHelper::isJson($jsonString));
        $this->assertFalse(StringHelper::isJson($nonJsonString));
    }

    public function testClearString()
    {

    }
}