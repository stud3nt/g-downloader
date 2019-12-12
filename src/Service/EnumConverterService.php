<?php

namespace App\Service;

use App\Enum\Base\Enum;
use App\Utils\AppHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EnumConverterService
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $enumClasses
     * 
     * @throws \Exception
     * @return bool
     */
    public function convertEnumsToJson(array $enumClasses = []) : bool
    {
        $jsonData = [];

        if ($enumClasses) {
            /** @var Enum $enumClass */
            foreach ($enumClasses as $enumClassString) {
                $jsonData[$enumClassString]['constants'] = (new \ReflectionClass($enumClassString))->getConstants();
                $jsonData[$enumClassString]['data'] = $enumClassString::getData();
            }
        }

        $ds = DIRECTORY_SEPARATOR;

        $jsonEnumAngularDir = AppHelper::getAngularSourceDir().'src'.$ds.'assets'.$ds.'json'.$ds;
        $jsonEnumPublicDir = AppHelper::getPublicDir().$ds.'app'.$ds.'assets'.$ds.'json'.$ds;

        $jsonEnumAngularFile = $jsonEnumAngularDir.$ds.'enums.json';
        $jsonEnumPublicFile = $jsonEnumPublicDir.$ds.'enums.json';

        $encodedJson = json_encode($jsonData);
        $currentJsonEnumDate = file_exists($jsonEnumAngularFile)
            ? (new \DateTime())->setTimestamp(filemtime($jsonEnumAngularFile))
            : null;

        $refreshTime = $this->container->getParameter('json_cache_refresh_time');

        if (!$currentJsonEnumDate || $currentJsonEnumDate->modify('+'.$refreshTime) < AppHelper::getCurrentDate()) {
            file_put_contents($jsonEnumAngularFile, $encodedJson);

            if (file_exists($jsonEnumPublicDir)) {
                file_put_contents($jsonEnumPublicFile, $encodedJson);
            }
        }

        return true;
    }
}