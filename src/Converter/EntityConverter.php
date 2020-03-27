<?php

namespace App\Converter;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Enum\EntityAnnotationVariables;
use App\Enum\EntityConvertType;
use App\Model\AbstractModel;
use App\Utils\StringHelper;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class EntityConverter extends BaseConverter
{
    /** @var AnnotationReader */
    protected $annotationReader;

    private $entity;

    /** @var ObjectManager */
    public $em;

    private $convertType = EntityConvertType::Array;

    private $converterOptions = [];

    public function __construct($converterOptions = [])
    {
        $this->annotationReader = new AnnotationReader();
        $this->converterOptions = $converterOptions;
    }

    /**
     * @required
     * @param ObjectManager $em
     * @return EntityConverter
     */
    public function setEntityManager(ObjectManager $em): self
    {
        $this->em = $em;

        return $this;
    }

    public function loadEntity(AbstractEntity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function setConvertType(string $convertType)
    {
        $this->convertType = $convertType;

        return $this;
    }

    /**
     * Converts specified entity to array|stdClass|json
     *
     * @param AbstractEntity|AbstractEntity[] $entity - entity to conversion
     * @param string|null $modelConvertName - convert model name (optional)
     * @param int $maxDepth - max depth of conversion relations (default: 2)
     * @return mixed
     * @throws \ReflectionException
     */
    public function convert(&$entity, string $modelConvertName = null)
    {
        if (is_array($entity) || $entity instanceof \Doctrine\Common\Collections\ArrayCollection || $entity instanceof \Doctrine\ORM\PersistentCollection) {
            $arrayEntity = [];

            foreach ($entity as $key => $e) {
                $arrayEntity[$key] = $this->convertSingleRow($e, $modelConvertName);
            }

            return $arrayEntity;
        }

        return $this->convertSingleRow($entity, $modelConvertName);
    }

    /**
     * @param AbstractEntity $entity
     * @param string|null $modelConvertName
     * @param int $maxDepth
     * @return array|false|mixed|string|null
     * @throws \ReflectionException
     */
    protected function convertSingleRow(AbstractEntity $entity, string $modelConvertName = null)
    {
        $this->loadEntity($entity);

        switch ($this->convertType) {
            case EntityConvertType::StdClass:
                $json = json_encode($this->convertEntityToArray($modelConvertName));
                return json_decode($json, false);
                break;

            case EntityConvertType::Array:
                return $this->convertEntityToArray($modelConvertName);
                break;

            case EntityConvertType::JsonArray:
                return json_encode($this->convertEntityToArray($modelConvertName));
                break;
        }

        return null;
    }

    /**
     * Sets data to entity
     *
     * @param $data
     * @param $entity
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function setData($data, AbstractEntity &$entity, string $modelConvertName = null)
    {
        $this->loadEntity($entity);

        if ($data instanceof \stdClass || $data instanceof AbstractModel) {
            $entityData = json_decode(json_encode($data), true);
        } elseif (StringHelper::isJson($data)) {
            $entityData = json_decode($data, true);
        } else {
            $entityData = $data;
        }

        $actionVariables = $this->getObjectVariables([EntityAnnotationVariables::Writable => true]);

        if ($actionVariables) {
            foreach ($actionVariables as $variableName => $variableConfig) {
                $rawVariable = $entityData[$variableName] ?? null;
                $this->setEntityVariableValue($variableName, $variableConfig, $rawVariable);
            }
        }

        return $this->entity;
    }

    /**
     * Converts current parser to array;
     *
     * @param $convertName;
     * @return array
     * @throws \ReflectionException
     */
    protected function convertEntityToArray(string $convertName = null) : array
    {
        $convertVariables = $this->getObjectVariables([EntityAnnotationVariables::Convertable => true]);
        $variablesArray = [];

        if ($convertVariables) {
            foreach ($convertVariables as $variableName => $variableConfig) {
                if (!in_array($convertName, $variableConfig->convertNames) && !$variableConfig->inAllConvertNames) {
                    continue;
                }

                $value = $this->getEntityVariableValue($variableName, $variableConfig);
                $variablesArray[$variableName] = $value;
            }
        }

        return $variablesArray;
    }

    /**
     * Get parser variables from entity;
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getObjectVariables(array $annotationsFilter = []) : array
    {
        $convertVariablesArray = [];
        $entityMethods = get_class_methods($this->entity);

        if ($entityMethods) {
            $reflectionClass = new \ReflectionClass(get_class($this->entity));

            foreach ($entityMethods as $methodKey => $methodName) {
                if (substr($methodName, 0, 3) === 'get') {
                    $variableName = lcfirst(substr($methodName, 3));

                    if ($reflectionClass->hasProperty($variableName)) {
                        $variableProperty = $reflectionClass->getProperty($variableName);
                        $variableAnnotations = $this->annotationReader->getPropertyAnnotation(
                            $variableProperty,
                            EntityVariable::class
                        );

                        if (is_object($variableAnnotations)) {
                            if ($annotationsFilter) {
                                foreach ($annotationsFilter as $annotationName => $expectedAnnotationValue) {
                                    if (!method_exists($variableAnnotations, $annotationName)) {
                                        continue;
                                    }

                                    $annotationValue = $variableAnnotations->{$annotationName};

                                    if ((is_array($annotationValue) && !in_array($expectedAnnotationValue, $annotationValue) && !in_array('default', $annotationValue))
                                        ||
                                        (!is_array($annotationValue) && $annotationValue != $expectedAnnotationValue && $annotationValue !== 'default')
                                    ) {
                                        continue;
                                    }
                                }
                            }

                            $convertVariablesArray[$variableName] = json_decode(json_encode($variableAnnotations));
                        }
                    }
                }
            }
        }

        return $convertVariablesArray;
    }

    protected function getEntityVariableValue(string $variableName, \stdClass $variableConfig)
    {
        $entityGetter = 'get'.ucfirst($variableName);

        if (!method_exists($this->entity, $entityGetter)) {  // method does not exists in entity
            throw new MethodNotImplementedException(
                'Getter method "'.$entityGetter.'" does not exists in class '.get_class($this->entity)
            );
        }

        $value = $this->entity->$entityGetter();

        /** @var BaseConverter $variableConverter */
        if ($value && $variableConfig->converter) {
            $variableConverterClass = 'App\\Converter\\'.$variableConfig->converter.'Converter';
            $variableConverter = new $variableConverterClass($variableConfig->converterOptions);

            if (method_exists($variableConverter, 'setEntityManager'))
                $variableConverter->setEntityManager($this->em);

            if (is_array($value)) {
                $newValue = [];

                foreach ($value as $v) {
                    $newValue[] = $variableConverter->convertFromEntityValue($v);
                }

                $value = $newValue;
            } else {
                $value = $variableConverter->convertFromEntityValue($value);
            }
        }

        return $value;
    }

    protected function setEntityVariableValue(string $variableName, \stdClass $variableConfig, $value)
    {
        if ($variableConfig->writable !== true) {
            return;
        }

        if (substr($variableName, -1, 1) === 's') {
            $adderVariableName = substr($variableName, 0, strlen($variableName) - 1);
        } elseif (substr($variableName, -2, 2) === 'es' && strlen($variableName) > 4) {
            $adderVariableName = substr($variableName, 0, strlen($variableName) - 2);
        } else {
            $adderVariableName = $variableName;
        }

        $entitySetter = 'set'.ucfirst($variableName);
        $entityAdder = 'add'.ucfirst($adderVariableName);

        if (!method_exists($this->entity, $entitySetter) && !method_exists($this->entity, $entityAdder)) { // method does not exists in entity
            throw new MethodNotImplementedException(
                'Setter method: "'.$entitySetter.'" and "'.$entityAdder.'" does not exists in class '.get_class($this->entity)
            );
        }

        /** @var BaseConverter $variableConverter */
        if ($variableConfig->converter) {
            $variableConverterClass = 'App\\Converter\\'.$variableConfig->converter.'Converter';
            $variableConverter = new $variableConverterClass($variableConfig->converterOptions);

            if (method_exists($variableConverter, 'setEntityManager'))
                $variableConverter->setEntityManager($this->em);

            if ($variableConfig->type === 'array') {
                $newValue = [];

                foreach ($value as $k => $v) {
                    $newValue[$k] = $variableConverter->convertToEntityValue($v);
                }

                $value = $newValue;
            } else {
                $value = $variableConverter->convertToEntityValue($value);
            }
        } else {
            if ($value === 'null') {
                $value = null;
            }

            switch ($variableConfig->type) {
                case 'array':
                    $value = (!is_array($value)) ? json_decode($value, true) : $value;
                    break;

                case 'boolean':
                    if (is_string($value)) {
                        $value = ($value === 'true' || $value === true);
                    } elseif (is_numeric($value)) {
                        $value = ($value === 1 || $value === '1');
                    } elseif (!is_bool($value)) {
                        $value = (bool)$value;
                    }
                    break;

                case 'integer':
                    $value = (int)trim($value);
                    break;

                case 'stdClass':
                    if (is_array($value)) {
                        $value = json_decode(json_encode($value), false);
                    } elseif (is_string($value) && StringHelper::isJson($value)) {
                        $value = json_decode($value, false);
                    }
                    break;
            }
        }

        if ($variableConfig->type === 'array') {
            $this->entity->$entitySetter(new ArrayCollection());

            foreach ($value as $v) {
                $this->entity->$entityAdder($v);
            }
        } else {
            $this->entity->$entitySetter($value);
        }
    }

    /**
     * @param $value
     * @return mixed
     * @throws \ReflectionException
     */
    public function convertToEntityValue($value)
    {
        if (property_exists($this->converterOptions, 'class') && !empty($value)) {
            if (is_array($value) && array_key_exists('id', $value))
                $entityId = $value['id'];
            elseif (is_object($value) && property_exists($value, 'id'))
                $entityId = $value->id;
            else
                $entityId = 0;

            if ($entityId && (int)$entityId > 0)
                $entity = $this->em->getRepository($this->converterOptions->class)->findOneBy(['id' => $entityId]);
            if (!$entityId || !$entity)
                $entity = new $this->converterOptions->class();

            $entityConverter = new EntityConverter();
            $entityConverter->setEntityManager($this->em);
            $entityConverter->setData($value, $entity);

            return $entity;
        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed
     * @throws \ReflectionException
     */
    public function convertFromEntityValue($value)
    {
        if (property_exists($this->converterOptions, 'class')) {
            if (($value instanceof PersistentCollection && $value->isEmpty()) || ($value instanceof ArrayCollection && $value->isEmpty()) || empty($value)) {
                return $value;
            } else {
                $entityConverter = new EntityConverter();
                $entityConverter->setEntityManager($this->em);

                if (is_array($value) || $value instanceof PersistentCollection || $value instanceof ArrayCollection) {
                    $newValue = [];

                    foreach ($value as $key => $v)
                        $newValue[$key] = $entityConverter->convert($v);

                    return $newValue;
                } elseif ($value instanceof AbstractEntity) {
                    return $entityConverter->convert($value);
                }
            }
        }

        return $value;
    }
}