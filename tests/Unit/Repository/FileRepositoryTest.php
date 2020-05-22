<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Base\AbstractEntity;
use App\Entity\Parser\File;
use App\Enum\FileStatus;
use App\Enum\FileType;
use App\Enum\ParserType;
use App\Repository\FileRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileRepositoryTest extends KernelTestCase
{
    /** @var ContainerInterface */
    protected $containerInstance;

    /** @var FileRepository */
    protected $fileRepository;

    protected $parserFiles = [
        ParserType::Reddit => [
            'hyNZnzM_ks1VxqB9AGHgUnFQ1ytUKaupoWv0bb_kNq0',
            'qHpRd2JROtknD_0Wd4-haEi5y0n8Jprqgz_XgEtNIKg',
            '9OQl2EOwXQYJ9TVNz4MViUD3CfT7H2vFZhUrf7dAnGw',
            'K_uaIW5wLzgu4EqHFjWRb3LRV34SchcdR4PtzSmYvHM'
        ],
        ParserType::Boards4chan => [
            '1589900943712',
            '1589712729730',
            '1578099096430',
            '1578342712180'
        ]
    ];

    public function setUp(): void
    {
        $this->containerInstance = self::bootKernel()->getContainer();
        $this->fileRepository = $this->containerInstance->get(FileRepository::class);
    }

    public function testGetQueryBuilder()
    {
        $qb = $this->fileRepository->getQb();
        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }

    public function testGetFilesQueryBuilder()
    {
        $qb = $this->fileRepository->getFilesQb([
            'limit' => 10
        ]);
        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }

    public function testGetStoredFilesArray()
    {
        foreach ($this->parserFiles as $parserName => $identifiersArray) {
            $storedArray = $this->fileRepository->getStoredFilesArray($parserName, $identifiersArray);

            $this->assertIsArray($storedArray);
            $this->assertNotEmpty($storedArray);
            $this->assertEquals(count($identifiersArray), count($storedArray));
        }
    }

    public function testGetFilesCountData()
    {
        $queuedFilesData = $this->fileRepository->getFilesCountData(FileStatus::Queued);
        $downloadedFilesData = $this->fileRepository->getFilesCountData(FileStatus::Downloaded);

        $this->assertIsArray($queuedFilesData);
        $this->assertIsArray($downloadedFilesData);

        $this->assertArrayHasKey('totalCount', $queuedFilesData);
        $this->assertArrayHasKey('totalCount', $downloadedFilesData);
        $this->assertArrayHasKey('totalSize', $queuedFilesData);
        $this->assertArrayHasKey('totalSize', $downloadedFilesData);
        $this->assertIsNumeric($queuedFilesData['totalCount']);
        $this->assertIsNumeric($queuedFilesData['totalSize']);
    }

    public function testGetQueuedFiles()
    {
        $arrayFiles = $this->fileRepository->getQueuedFiles(5, true);
        $entityFiles = $this->fileRepository->getQueuedFiles(5, false);

        $this->assertIsArray($arrayFiles);
        $this->assertIsArray($entityFiles);

        foreach ($arrayFiles as $file) {
            $this->assertIsArray($file);
            $this->assertArrayHasKey('id', $file);
            $this->assertIsNumeric($file['id']);
        }

        foreach ($entityFiles as $file) {
            $this->assertInstanceOf(AbstractEntity::class, $file);
            $this->assertTrue(method_exists($file, 'getId'));
            $this->assertIsInt($file->getId());
        }
    }

    public function testGetSimilarFiles()
    {
        $randomFiles = $this->fileRepository->getRandomFiles(10);

        /** @var File $randomFile */
        foreach ($randomFiles as $randomFile) {
            $similarFiles = $this->fileRepository->getSimilarFiles($randomFile);

            if (!$similarFiles) {
                $this->assertTrue(true);
                continue;
            }

            /** @var File $similarFile */
            foreach ($similarFiles as $similarFile) {
                if ($randomFile->getType() === FileType::Image)
                    $this->assertNotNull($similarFile->getHexHash());
            }

        }

        $this->assertTrue(true);
    }
}