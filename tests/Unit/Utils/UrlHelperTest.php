<?php

namespace App\Tests\Unit\Utils;

use App\Utils\AppHelper;
use App\Utils\UrlHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlHelperTest extends KernelTestCase
{
    private $namesTestCases = [
        '5\'8" 155, 37 years old' => '5\'8\'\' 155, 37 years old',
        'my first drop ♥️ 18' => 'my first drop - 18',
        'Eva & Katya' => 'Eva & Katya',
        '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback 😊' => '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback'
    ];

    public function testPrepareLocalUrl()
    {
        $fileUrl = AppHelper::getPublicDir().'test'.DIRECTORY_SEPARATOR.'BwjLv73.jpg';
        $localUrl = UrlHelper::prepareLocalUrl($fileUrl);

        $this->assertIsString($localUrl);
        $this->assertNotContains(AppHelper::getPublicDir(), $localUrl);
    }

    public function testCheck403()
    {
        $router = $container = self::bootKernel()->getContainer()->get('router');
        $sample403Url = 'http://test.g-downloader.com'.$router->generate('app_test_403');
        $sample404Url = 'http://test.g-downloader.com'.$router->generate('app_test_404');

        $this->assertTrue(UrlHelper::check403($sample403Url));
        $this->assertFalse(UrlHelper::check403($sample404Url));
    }

    public function testFixUrl()
    {
        $inputUrl = 'https://cdn.imagefap.com/im ages/full/74/643/643085587.jpg?end=1590058448&secure=073d3a822f1d97fad5cc7';

        $expectedOutputWithQuery = 'https://cdn.imagefap.com/im%20ages/full/74/643/643085587.jpg?end=1590058448&secure=073d3a822f1d97fad5cc7';
        $expectedOutputWithoutQuery = 'https://cdn.imagefap.com/im%20ages/full/74/643/643085587.jpg';

        $outputWithQuery = UrlHelper::fixUrl($inputUrl, false);
        $outputWithoutQuery = UrlHelper::fixUrl($inputUrl, true);

        $this->assertisString($outputWithQuery);
        $this->assertisString($outputWithoutQuery);

        $this->assertEquals($outputWithQuery, $expectedOutputWithQuery);
        $this->assertEquals($outputWithoutQuery, $expectedOutputWithoutQuery);
    }
}