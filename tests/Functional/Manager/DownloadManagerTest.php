<?php

namespace App\Tests\Functional\Manager;

use App\Converter\ModelConverter;
use App\Entity\User;
use App\Factory\ParsedFileFactory;
use App\Factory\RedisFactory;
use App\Manager\DownloadManager;
use App\Manager\Object\FileManager;
use App\Manager\UserManager;
use App\Model\ParsedFile;
use App\Tests\Functional\Manager\Base\BasicManagerTestCase;
use App\Utils\StringHelper;
use App\Utils\TestsHelper;

class DownloadManagerTest extends BasicManagerTestCase
{
    /** @var DownloadManager */
    protected $manager;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var \Predis\ClientInterface|\Redis|\RedisCluster */
    protected $redis;

    /** @var User|null */
    protected $user = null;

    /** @var string */
    protected $redisKey = '';

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->manager = $this->loadManager(DownloadManager::class);
        $this->manager->init($this->loadManager(FileManager::class));

        $this->user = $this->loadManager(UserManager::class)
            ->getByUsernameOrEmail(TestsHelper::$testAdminUser['username']);

        $this->redis = (new RedisFactory())->initializeConnection();
        $this->redisKey = $this->user->getDownloaderRedisKey();

        $this->modelConverter = new ModelConverter();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \ReflectionException
     */
    public function testCreateStatusData()
    {
        $this->manager->createStatusData($this->user, null, null);

        $this->assertTrue($this->redis->exists($this->redisKey));

        $redisData = $this->redis->get($this->redisKey);

        $this->assertNotNull($redisData);
        $this->assertTrue(StringHelper::isJson($redisData));

        $decodedData = json_decode($redisData, true);

        $this->assertIsArray($decodedData);
    }

    public function testIncreaseQueueByParsedFile()
    {
        $parsedFile = $this->createParsedFile();

        $this->manager->increaseQueueByParsedFile($this->user, $parsedFile);

        $this->assertTrue($this->redis->exists($this->redisKey));
    }

    public function testDecreaseQueueByParsedFile()
    {
        $parsedFile = $this->createParsedFile();

        $this->manager->decreaseQueueByParsedFile($this->user, $parsedFile);

        $this->assertTrue($this->redis->exists($this->redisKey));
    }

    private function createParsedFile(): ParsedFile
    {
        return (new ParsedFileFactory())->buildFromRequestData(
            TestsHelper::generateFileArray()
        );
    }
}