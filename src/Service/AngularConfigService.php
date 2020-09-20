<?php

namespace App\Service;

use App\Converter\ModelConverter;
use App\Enum\ParserType;
use App\Enum\SettingsGroup;
use App\Enum\SettingsLevel;
use App\Manager\SettingsManager;
use App\Model\SettingsModel;
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
        $modelConverter = new ModelConverter();
        $parserSettingsModel = $this->settingsManager->getSettings([
            'group' => SettingsGroup::Parser,
            'level' => SettingsLevel::Public
        ]);
        $parserSettings = $modelConverter->convert($parserSettingsModel);

        $config = json_encode([
            'menu' => $this->getMenuStructure($parserSettingsModel),
            'routing' => $this->getRouting(),
            'parsers' => $parserSettings['parsers']
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

    protected function getMenuStructure(SettingsModel $settings): array
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
                            'parserName' => ParserType::Imagefap,
                            'nodeLevel' => $settings->getParserSetting(ParserType::Imagefap, 'initialLevel')
                        ],
                        'label' => 'ImageFap'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::Boards4chan,
                            'nodeLevel' => $settings->getParserSetting(ParserType::Boards4chan, 'initialLevel')
                        ],
                        'label' => 'Boards 4Chan'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::HentaiFoundry,
                            'nodeLevel' => $settings->getParserSetting(ParserType::HentaiFoundry, 'initialLevel')
                        ],
                        'label' => 'Hentai-Foundry'
                    ],
                    [
                        'route' => 'app_parser',
                        'routeParams' => [
                            'parserName' => ParserType::Reddit,
                            'nodeLevel' => $settings->getParserSetting(ParserType::Reddit, 'initialLevel')
                        ],
                        'label' => 'Reddit'
                    ]
                ]
            ],
            [
                'route' => null,
                'label' => 'Downloading files',
                'icon' => 'fa-download',
                'childs' => [
                    [
                        'route' => 'app_download_queue',
                        'label' => 'Queue panel'
                    ],
                    [
                        'route' => 'app_download_list',
                        'label' => 'Downloaded list'
                    ],
                ]
            ],
            [
                'route' => null,
                'label' => 'Lists',
                'icon' => 'fa-list',
                'childs' => [
                    [
                        'route' => 'app_lists',
                        'routeParams' => [
                            'listName' => 'categories'
                        ],
                        'label' => 'Categories'
                    ],
                    [
                        'route' => 'app_lists',
                        'routeParams' => [
                            'listName' => 'tags'
                        ],
                        'label' => 'Tags'
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