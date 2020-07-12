<?php

namespace App\Tests\Functional\Manager;

use App\Entity\Tag;
use App\Manager\TagManager;
use App\Model\ParserRequest;
use App\Tests\Functional\Manager\Base\BasicManagerTestCase;
use App\Utils\TestsHelper;

class TagManagerTest extends BasicManagerTestCase
{
    /** @var TagManager */
    protected $manager;

    /** @var int */
    protected $tagsCount = 0;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->manager = $this->loadManager(TagManager::class);
        $this->tagsCount = $this->manager->getCount();
    }

    public function testGetTagsList()
    {
        $tagsList = $this->manager->getTagsList();

        if ($this->tagsCount === 0) {
            $this->assertEmpty($tagsList);
        } else {
            $this->assertNotEmpty($tagsList);
            $this->assertIsArray($tagsList);

            $tag = $tagsList[key($tagsList)];

            $this->assertNotEmpty($tag);
            $this->assertInstanceOf(Tag::class, $tag);
            $this->assertIsInt($tag->getId());
        }
    }

    public function testGetTagsModels()
    {
        $tagsModels = $this->manager->getTagsModels();

        if ($this->tagsCount === 0) {
            $this->assertEmpty($tagsModels);
        } else {
            $this->assertNotEmpty($tagsModels);
            $this->assertIsArray($tagsModels);

            $tag = $tagsModels[key($tagsModels)];

            $this->assertNotEmpty($tag);
            $this->assertInstanceOf(\App\Model\Tag::class, $tag);
        }
    }

    public function testCompleteTagsList()
    {
        $parserRequestModel = new ParserRequest();
        $parserRequestData = TestsHelper::generateParserRequestArray();
        $parserRequestData['tags'] = [];

        $this->getModelConverter()->setData($parserRequestData, $parserRequestModel);

        $this->assertEmpty($parserRequestModel->getTags());

        $this->manager->completeTagsList($parserRequestModel);

        $requestTags = $parserRequestModel->getTags();

        $this->assertIsArray($requestTags);
        $this->assertNotEmpty($requestTags);

        $tag = $requestTags[key($requestTags)];

        $this->assertNotNull($tag);
        $this->assertInstanceOf(\App\Model\Tag::class, $tag);
    }

}