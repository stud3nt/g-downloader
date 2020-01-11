<?php

namespace App\Manager;

use App\Converter\EntityConverter;
use App\Entity\Setting;
use App\Enum\SettingsGroup;
use App\Enum\SettingsType;
use App\Manager\Base\EntityManager;
use App\Model\SettingsModel;
use App\Repository\SettingsRepository;
use App\Utils\StringHelper;
use Doctrine\ORM\AbstractQuery;

class SettingsManager extends EntityManager
{
    protected $entityName = 'Setting';

    /** @var SettingsRepository */
    protected $repository;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @required */
    public function setEntityConverter(EntityConverter $entityConverter)
    {
        $this->entityConverter = $entityConverter;

        return $this;
    }

    /**
     * @param array $parameters - array with search params
     * @param bool $simpleMode
     * @return array
     * @throws \ReflectionException
     */
    public function getSettings(array $parameters = []): SettingsModel
    {
        $settingsData = $this->repository->getQb($parameters)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;

        return $this->convertSettingsToModel($settingsData);
    }

    /**
     * Gets settings for specified parser (parser settings + common data like download folders and compression settings)
     *
     * @param string $parserName
     * @return SettingsModel
     */
    public function getParserSettings(string $parserName): SettingsModel
    {
        $settingsData = $this->repository->getQb()
            ->andWhere('s.group = :parserGroup AND s.type = :parserName')
            ->setParameter('parserGroup', SettingsGroup::Parser)
            ->setParameter('parserName', $parserName)
            ->orWhere('s.group = :commonGroup')
            ->setParameter('commonGroup', SettingsGroup::Common)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;

        return $this->convertSettingsToModel($settingsData);
    }

    protected function convertSettingsToModel(array $settingsData = []): SettingsModel
    {
        $settingsModel = new SettingsModel();

        if ($settingsData) {
            foreach ($settingsData as $setting) {
                $settingName = StringHelper::underscoreToCamelCase($setting['name']);

                if ($setting['group'] === SettingsGroup::Parser) {
                    $settingsModel->setParserSetting($setting['type'], $settingName, $setting['value']);
                } else {
                    $settingsSetter = 'set'.ucfirst($setting['group']).'Setting';
                    $settingsModel->$settingsSetter($settingName, $setting['value']);
                }
            }
        }

        return $settingsModel;
    }
}
