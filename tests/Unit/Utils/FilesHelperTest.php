<?php

namespace App\Tests\Unit\Utils;

use App\Enum\FileType;
use App\Utils\AppHelper;
use App\Utils\FilesHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilesHelperTest extends KernelTestCase
{
    private $testImageFile;
    private $testVideoFile;

    private $namesTestCases = [
        '5\'8" 155, 37 years old' => '5\'8\'\' 155, 37 years old',
        'my first drop ♥️ 18' => 'my first drop - 18',
        'Eva & Katya' => 'Eva & Katya',
        '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback 😊' => '(f) Early 30\'s. 5ft8 56kgs. I\'m always for feedback'
    ];

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->testImageFile = AppHelper::getDataDir().'test'.DIRECTORY_SEPARATOR.'BwjLv73.jpg';
        $this->testVideoFile = AppHelper::getDataDir().'test'.DIRECTORY_SEPARATOR.'HN3pfFPs37hxghO5FkDVshUx3e4WeCznMwn7Y3XWuWU.mp4';
    }

    public function testGetFileName()
    {
        $imageFileName = FilesHelper::getFileName($this->testImageFile);
        $imageFileNameWithExtension =  FilesHelper::getFileName($this->testImageFile, true);

        $this->assertIsString($imageFileName);
        $this->assertIsString($imageFileNameWithExtension);

        $this->assertEquals('BwjLv73', $imageFileName);
        $this->assertEquals('BwjLv73.jpg', $imageFileNameWithExtension);
    }

    public function testGetFileExtension()
    {
        $imageExtension = FilesHelper::getFileExtension($this->testImageFile);
        $viedoExtension = FilesHelper::getFileExtension($this->testVideoFile);

        $this->assertIsString($imageExtension);
        $this->assertIsString($viedoExtension);

        $this->assertEquals('jpg', $imageExtension);
        $this->assertEquals('mp4', $viedoExtension);
    }

    public function testGetFileMimeType()
    {
        $imageMimeType = FilesHelper::getFileMimeType($this->testImageFile);
        $videoMimeType = FilesHelper::getFileMimeType($this->testVideoFile);

        $this->assertIsString($imageMimeType);
        $this->assertIsString($videoMimeType);

        $this->assertEquals('image/jpeg', $imageMimeType);
        $this->assertEquals('video/mp4', $videoMimeType);
    }

    public function testFilenameFromString()
    {
        foreach ($this->namesTestCases as $badFileName => $expectedProperName) {
            $fixedName = FilesHelper::fileNameFromString($badFileName);

            $this->assertIsString($fixedName);
            $this->assertEquals($expectedProperName, $fixedName);
        }
    }

    public function testFolderNameFromString()
    {
        foreach ($this->namesTestCases as $badFileName => $expectedProperName) {
            $fixedName = FilesHelper::folderNameFromString($badFileName);

            $this->assertIsString($fixedName);
            $this->assertEquals($expectedProperName, $fixedName);
        }
    }
}