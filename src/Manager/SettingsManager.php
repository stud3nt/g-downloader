<?php

namespace App\Manager;

use App\Converter\EntityConverter;
use App\Entity\Setting;
use App\Enum\EntityConvertType;
use App\Manager\Base\EntityManager;
use App\Repository\SettingsRepository;

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
    public function getSettings(array $parameters = [], bool $simpleMode = false) : array
    {
        $settings = [];
        $rawSettings = $this->repository->getQb($parameters)
            ->getQuery()->execute();

        if ($rawSettings) {
            /** @var Setting $rawSetting */
            foreach ($rawSettings as $rawSetting) {
                $settingKey = $rawSetting->getGroup().'_'.strtolower($rawSetting->getName());
                $settings[$settingKey] = $simpleMode
                    ? $rawSetting->getValue()
                    : $this->entityConverter->setConvertType(EntityConvertType::Array)
                        ->convert($rawSetting, 'basic_settings');
            }
        }

        return $settings;
    }
}
