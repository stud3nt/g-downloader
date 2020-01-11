<?php


namespace App\Service;

use App\Manager\SettingsManager;
use App\Parser\Base\ParserInterface;
use App\Utils\AppHelper;
use App\Utils\StringHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ParserService
{
    /** @var SettingsManager */
    protected $settingsManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(SettingsManager $settingsManager, TokenStorageInterface $tokenStorage)
    {
        $this->settingsManager = $settingsManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function loadParser($parser)
    {
        if (is_array($parser)) {
            $array = [];

            foreach ($parser as $parserName) {
                if ($parser = $this->parserFactory($parserName))
                    $array[] = $parser;
            }

            return $array;
        } else {
            return $this->parserFactory($parser);
        }
    }

    protected function parserFactory(string $parserName): ?ParserInterface
    {
        $parserClass = 'App\\Parser\\'.ucfirst(StringHelper::underscoreToCamelCase($parserName)).'Parser';
        $parserSettings = $this->settingsManager->getParserSettings($parserName);
        $currentUser = AppHelper::getCurrentUser($this->tokenStorage);

        return class_exists($parserClass)
            ? new $parserClass($parserSettings, $currentUser)
            : null;
    }
}