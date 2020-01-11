<?php

namespace App\Controller\Api\Base;

use App\Converter\ModelConverter;
use App\Manager\Object\FileManager;
use App\Manager\Object\NodeManager;
use App\Manager\SettingsManager;
use App\Parser\Base\ParserInterface;
use App\Utils\StringHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Container\ContainerInterface;

class Controller extends BaseController
{
    /** @var SettingsManager */
    protected $settingsManager;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            NodeManager::class,
            ModelConverter::class,
            FileManager::class
        ]);
    }
}