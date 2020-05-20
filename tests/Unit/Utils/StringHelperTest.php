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
        'my first drop ♥️ 18' => 'my first drop - 18'
    ];

    private $camelCaseNames = [
        [
            'camelCase' => 'functionName',
            'underscore' => 'function_name'
        ],
        [
            'camelCase' => 'loremIpsumDolorEmit',
            'underscore' => 'lorem_ipsum_dolor_emit'
        ],
        [
            'camelCase' => 'testFunctionName1',
            'underscore' => 'test_function_name_1'
        ],
        [
            'camelCase' => 'function1',
            'underscore' => 'function_1'
        ],
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
        foreach ($this->clearStringTestCases as $inputString => $expectedOutputString) {
            $clearedString = StringHelper::clearString($inputString);

            $this->assertIsString($clearedString);
            $this->assertEquals($expectedOutputString, $clearedString);
        }
    }

    public function testCamelCaseToUnderscore()
    {
        foreach ($this->camelCaseNames as $cc) {
            $testedCamelCase = $cc['camelCase'];
            $expectedUnderscore = $cc['underscore'];
            $testedUnderscore = StringHelper::camelCaseToUnderscore($testedCamelCase);

            $this->assertIsString($testedUnderscore);
            $this->assertEquals($expectedUnderscore, $testedUnderscore);
        }
    }

    public function testUnderscoreToCamelCase()
    {
        foreach ($this->camelCaseNames as $cc) {
            $testedUnderscore = $cc['underscore'];
            $expectedCamelCase = $cc['camelCase'];

            $testedCamelCase = StringHelper::underscoreToCamelCase($testedUnderscore);

            $this->assertIsString($testedCamelCase);
            $this->assertEquals($expectedCamelCase, $testedCamelCase);
        }
    }

    public function testBasicCharactersStringOnly()
    {
        $inputString = 'Mik#ołajczyk ^ to były oficer z rozwiąza=nych w 2006 r Wojskowych#$ Służb Info$rmacyjnych';
        $expectedString = 'mik-o--ajczyk---to-by--y-oficer-z-rozwi--za-nych-w-2006-r-wojskowych---s--u--b-info-rmacyjnych';

        $testString = StringHelper::basicCharactersOnly($inputString);

        $this->assertIsString($inputString);
        $this->assertEquals($expectedString, $testString);
    }
}