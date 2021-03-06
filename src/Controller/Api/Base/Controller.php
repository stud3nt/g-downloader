<?php

namespace App\Controller\Api\Base;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\User;
use App\Factory\SerializerFactory;
use App\Manager\CategoryManager;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Manager\SettingsManager;
use App\Manager\TagManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

class Controller extends BaseController
{
    /** @var SettingsManager */
    protected $settingsManager;

    /** @var ContainerInterface */
    protected $container;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var NodeManager */
    protected $nodeManager;

    /** @var Serializer */
    protected $entitySerializer;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, SerializerFactory $serializerFactory)
    {
        $this->container = $container;

        $this->modelConverter = $this->get(ModelConverter::class);
        $this->nodeManager = $this->get(NodeManager::class);
        $this->fileManager = $this->get(FileManager::class);
        $this->entityConverter = $this->get(EntityConverter::class);
        $this->entityConverter->setEntityManager(
            $this->getDoctrine()->getManager()
        );

        $this->entitySerializer = $serializerFactory->getEntityNormalizer();
    }

    /** @required */
    public function setSettingsManager(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    protected function validateUser() : bool
    {
        return true;
    }

    protected function getRequestParam(Request $request, $paramKey = null)
    {
        $content = $this->getAllRequestContent($request, true);

        if (!empty($content)) {
            if (array_key_exists($paramKey, $content)) {
                return $content[$paramKey];
            }
        }

        return null;
    }

    protected function getAllRequestContent(Request $request, bool $assoc = false): array
    {
        $requestContent = $request->getContent();

        if (!empty($requestContent)) {
            return json_decode($requestContent, true);
        }

        return [];
    }

    protected function jsonError($data = null): JsonResponse
    {
        return $this->json([
            'status' => -1,
            'data' => $data
        ]);
    }

    protected function jsonSuccess($data = null): JsonResponse
    {
        return $this->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    protected function translate(string $message): string
    {
        return $this->get('translator')->trans($message);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            CategoryManager::class,
            NodeManager::class,
            ModelConverter::class,
            FileManager::class,
            EntityConverter::class,
            TagManager::class
        ]);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return object|null
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     *
     * @final
     */
    protected function getCurrentUser(): ?User
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}