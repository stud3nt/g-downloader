<?php

namespace App\Manager;

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
    public function completeCategoriesList(ParserRequest $parserRequest): ParserRequest
    {
        $parserRequest->setCategories([]);
        $categories = $this->getCategoriesList();

        if ($categories) {
            foreach ($categories as $categoryEntity) {
                $parserRequest->addCategory(
                    (new \App\Model\Category())->setFromEntity($categoryEntity)
                );
            }
        }

        return $parserRequest;
    }

    /**
     * Save category as new entry if not exists or haven't specified ID
     *
     * @param Category $category
     */
    public function updateEntity(Category $category): void
    {
        $finalCategory = $this->repository->findOneBy(['id' => $category->getId()]);

        if (!$finalCategory) { // category does not exists => save as new
            $this->save($category);
        } else {
            $finalCategory->setName($category->getName());
            $this->save($finalCategory);
        }
    }

    public function removeEntity(Category $category): bool
    {
        $finalCategory = $this->repository->findOneBy(['id' => $category->getId()]);

        if ($finalCategory)
            $this->remove($finalCategory);

        return true;
    }
}
