<?php

namespace App\Controller\Api;

use App\Controller\Api\Base\Controller;
use App\Factory\Entity\CategoryEntityFactory;
use App\Manager\CategoryManager;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends Controller
{
    /**
     * @Route("/api/lists/list", name="api_categories_list", methods={"GET"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function listing(): JsonResponse
    {
        $categories = $this->get(CategoryRepository::class)->findAll();

        return $this->jsonSuccess(
            $this->entityConverter->convert($categories)
        );
    }

    /**
     * @Route("/api/lists/create", name="api_categories_create", methods={"POST"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     * @throws \ReflectionException
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $categoryEntity = (new CategoryEntityFactory())->buildFromRequestData(
            $request->request->all()
        );

        $this->get(CategoryManager::class)->updateEntity($categoryEntity);

        return $this->jsonSuccess();
    }

    /**
     * @Route("/api/lists/delete", name="api_categories_delete", methods={"POST"}, options={"expose":true})
     * @IsGranted("ROLE_ADMIN")
     * @throws \ReflectionException
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $categoryEntity = (new CategoryEntityFactory())->buildFromRequestData(
            $request->request->all()
        );

        $this->get(CategoryManager::class)->removeEntity($categoryEntity);

        return $this->jsonSuccess();
    }
}