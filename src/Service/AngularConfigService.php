<?php

namespace App\Service;

use App\Enum\ParserType;
use App\Enum\SettingsLevels;
use App\Manager\SettingsManager;
use App\Utils\AppHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

class AngularConfigService
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var RouterInterface $router */
    protected $router;

    /** @var SettingsManager $settingsManager */
    protected $settingsManager;

    protected $jsonAngularConfigDirectory;
    protected $jsonAngularConfigFile;

    protected $jsonPublicConfigDirectory;
    protected $jsonPublicConfigFile;

    /** @var Filesystem $fs */
    protected $fs;

    public function __construct(ContainerInterface $container, RouterInterface $router, SettingsManager $settingsManager)
    {
        $this->container = $container;
        $this->router = $router;
        $this->settingsManager = $settingsManager;

        $this->fs = new Filesystem();

        $ds = DIRECTORY_SEPARATOR;

        $this->jsonAngularConfigDirectory = AppHelper::getAngularSourceDir().'src'.$ds.'assets'.$ds.'json';
        $this->jsonAngularConfigFile = $this->jsonAngularConfigDirectory.$ds.'angular-config.json';

        $this->jsonPublicConfigDirectory = AppHelper::getPublicDir().$ds.'app'.$ds.'assets'.$ds.'json';
        $this->jsonPublicConfigFile = $this->jsonPublicConfigDirectory.$ds.'angular-config.json';

        if (!file_exists($this->jsonAngularConfigDirectory)) {
            $this->fs->mkdir($this->jsonAngularConfigDirectory);
        }
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function generateInitialJsonConfigFile() : bool
    {
        $config = json_encode([
            'menu' => $this->getMenuStructure(),
            'routing' => $this->getRouting(),
            'parsers' => $this->getInitialParsersSettings()
        ]);

        $refreshTime = $this->container->getParameter('json_cache_refresh_time');
        $currentJsonEnumDate = file_exists($this->jsonAngularConfigFile)
            ? (new \DateTime())->setTimestamp(filemtime($this->jsonAngularConfigFile))
            : null;

        // old file or not exists -> UPDATE
        if (!$currentJsonEnumDate || $currentJsonEnumDate->modify('+'.$refreshTime) < AppHelper::getCurrentDate()) {
            file_put_contents($this->jsonAngularConfigFile, $config);

            if (file_exists($this->jsonPublicConfigDirectory)) { // if angular is already compiled
                file_put_contents($this->jsonPublicConfigFile, $config);
            }

            return true;
        }

        return false;
    }

    /**
     * @throws \ReflectionException
     */
    protected function getInitialParsersSettings()
    {
        return $this->settingsManager->getSettings([
            'level' => SettingsLevels::Initial
        ], true);
    }

    protected function getMenuStructure(): array
    {
        return [
            [
                'route' => 'app_index',
                'label' => 'Dashboard',
                'icon' => 'fa-dashboard',
                'childs' => []
            ],
            [
                'route' => null,
                'label' => 'Parsers',
                'icon' => 'fa-laptop',
                'childs' => [
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::Imagefap
                        ],
                        'label' => 'ImageFap'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::Boards4chan
                        ],
                        'label' => 'Boards 4Chan'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::HentaiFoundry
                        ],
                        'label' => 'Hentai-Foundry'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::Reddit
                        ],
                        'label' => 'Reddit'
                    ]
                ]
            ],
            [
                'route' => null,
                'label' => 'Users',
                'icon' => 'fa-users',
                'childs' => [
                    [
                        'route' => 'app_users_list',
                        'label' => 'List'
                    ],
                    [
                        'route' => 'app_users_groups',
                        'label' => 'Groups and privileges'
                    ]
                ]
            ],
            [
                'route' => 'app_settings',
                'label' => 'Settings',
                'icon' => 'fa-tasks',
                'childs' => []
            ]
        ];
    }

    protected function getRouting() : array
    {
        $collection = $this->router->getRouteCollection();
        $allRoutes = $collection->all();
        $ajaxRoutes = [];

        if ($allRoutes) { // routes exists
            foreach ($allRoutes as $routeName => $route) {
                if ($route->getOption('expose') === true) {
                    $defaults = $route->getDefaults();
                    $params = [];

                    if (array_key_exists('_controller', $defaults)) {
                        unset($defaults['_controller']);
                    }

                    if (!empty($defaults)) {
                        foreach ($defaults as $paramName => $defaultValue) {
                            $params[$paramName] = $paramName;
                        }
                    }

                    $ajaxRoutes[$routeName] = [
                        'name' => $routeName,
                        'path' => $route->getPath(),
                        'params' => $params,
                        'defaults' => $defaults
                    ];
                }
            }
        }

        return $ajaxRoutes;
    }

}