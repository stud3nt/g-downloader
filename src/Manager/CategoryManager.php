<?php

namespace App\Manager;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Category;
use App\Manager\Base\EntityManager;
use App\Model\ParserRequest;
use App\Repository\CategoryRepository;

class CategoryManager extends EntityManager
{
    protected $entityName = 'Category';

    /** @var CategoryRepository */
    protected $repository;

    public function getCategoriesList()
    {
        return $this->repository->findBy(
            ['active' => true],
            ['name' => 'ASC']
        );
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getCategoriesModels()
    {
        $categoriesModels = [];
        $categories = $this->getCategoriesList();

        if ($categories) {
            $modelConverter = new ModelConverter();

            /** @var Category $category */
            foreach ($categories as $category) {
                $categoryModel = new \App\Model\Category();
                $modelConverter->setData($category, $categoryModel);
                $categoriesModels[$category->getId()] = $categoryModel;
            }
        }

        return $categoriesModels;
    }

    /**
     * Completes parser request categories list;
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws \ReflectionException
     */
    public function completeCategoriesList(ParserRequest &$parserRequest): ParserRequest
    {
        if (!$parserRequest->getCategories()) {
            $categories = $this->getCategoriesList();

            if ($categories) {
                foreach ($categories as $categoryEntity) {
                    $parserRequest->addCategory(
                        (new \App\Model\Category())->setFromEntity($categoryEntity)
                    );
                }
            }
        }

        return $parserRequest;
    }

    public function createFromEntity(Category $category): bool
    {
        $searches = [];

        if ($category->getId())
            $searches['id'] = $category->getId();
        else
            $searches['name'] = $category->getName();

        /** @var $checkedCategory|null */
        $checkedCategory = $this->repository->findOneBy($searches);

        if ($checkedCategory) {
            $checkedCategory->setName(
                $category->getName()
            );

            $this->save($checkedCategory);
        } else {
            $this->save($category);
        }

        return true;
    }

    public function removeEntity(Category $category): bool
    {
        $properCategory = $this->repository->findOneBy([
            'id' => $category->getId()
        ]);

        if ($properCategory)
            $this->remove($properCategory);

        return true;
    }
}
