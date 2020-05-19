<?php

namespace App\Tests\Unit\Utils;

use App\Utils\AppHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppHelperTest extends KernelTestCase
{
    public function testCurrentUser()
    {
        $tokenStorageInterface = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull(AppHelper::getCurrentUser($tokenStorageInterface));
    }

    public function testGetCurrentDate()
    {
        $date = AppHelper::getCurrentDate();
        $now = new \DateTime();

        $this->assertInstanceof('DateTime', $date);
        $this->assertLessThan(2, $now->diff($date)->s);
    }

    public function testGetRootDir()
    {
        $rootDir = AppHelper::getRootDir();

        $this->assertIsString($rootDir);
        $this->assertFileExists($rootDir);
    }

    public function testGetAngularSourceDir()
    {
        $angularDir = AppHelper::getAngularSourceDir();

        $this->assertIsString($angularDir);
        $this->assertStringContainsString('angular-source', $angularDir);
    }

    public function testGetPublicDir()
    {
        $publicDir = AppHelper::getPublicDir();

        $this->assertIsString($publicDir);
        $this->assertStringContainsString('public', $publicDir);
    }

    public function testGetDataDir()
    {
        $dataDir = AppHelper::getDataDir();

        $this->assertIsString($dataDir);
        $this->assertStringContainsString('data', $dataDir);
    }

    public function testCheckPublicDir()
    {
        AppHelper::checkPublicDir();

        $this->assertTrue(defined('PUBLIC_DIR'));
    }
}