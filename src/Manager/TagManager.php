<?php

namespace App\Manager;

use App\Converter\ModelConverter;
use App\Entity\Tag;
use App\Manager\Base\EntityManager;
use App\Model\ParserRequest;
use App\Repository\CategoryRepository;

class TagManager extends EntityManager
{
    protected $entityName = 'Tag';

    /** @var CategoryRepository */
    protected $repository;

    public function getTagsList()
    {
        return $this->repository->findBy([], ['name' => 'ASC']);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getTagsModels()
    {
        $tags = $this->getTagsList();
        $tagsModels = [];

        if ($tags) {
            $modelConverter = new ModelConverter();

            /** @var Tag $tag */
            foreach ($tags as $tag) {
                $tagModel = new \App\Model\Tag();
                $modelConverter->setData($tag, $tagModel);
                $tagsModels[$tag->getId()] = $tagModel;
            }
        }

        return $tagsModels;
    }

    /**
     * Completes parser request tags list;
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function completeTagsList(ParserRequest $parserRequest): ParserRequest
    {
        $parserRequest->setTags([]);
        $tags = $this->getTagsList();

        if ($tags) {
            foreach ($tags as $tag) {
                $parserRequest->addTag(
                    (new \App\Model\Tag())->setFromEntity($tag, \App\Model\Tag::class)
                );
            }
        }

        return $parserRequest;
    }
}
