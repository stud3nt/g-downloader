<?php

namespace App\Converter;

use App\Annotation\Serializer\ObjectVariable;
use App\Converter\Base\SerializerOptions;
use App\Utils\StringHelper;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class ObjectSerializer
{
    const FORMAT_ARRAY = 'array';
    const FORMAT_JSON = 'json';

    const MODE_SERIALIZE = 'serialize';
    const MODE_DESERIALIZE = 'deserialize';

    protected AnnotationReader $annotationReader;

    /** @var ObjectVariable[] */
    private array $objectVariables = [];

    // converted object
    private $object = null;

    // format output on serializing / format input on deserializing;
    private string $format;
    
    // serialized data;
    private $serializedData = [];

    // formatting options;
    private SerializerOptions $options;

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Serializing object to specified format;
     *
     * @param $object
     * @param string $format
     * @returns mixed
     * @throws \ReflectionException
     */
    public function serialize($object, string $format = self::FORMAT_ARRAY, array $options = [])
    {
        $this->serializedData = [];

        if (is_iterable($object) || is_array($object)) {
            foreach ($object as $row) {
                $this->serializedData[] = $this->serializeObject($row, $format, $options);
            }
        } else {
            $this->serializedData = $this->serializeObject($object, $format, $options);
        }

        return $this->getSerializedData($format);
    }

    private function serializeObject($object, string $format = self::FORMAT_ARRAY, array $options = [])
    {
        $this->loadObject($object);
        $this->setFormat($format);
        $this->loadOptions($options);

        $this->getObjectProperties();

        $objectValues = [];

        if ($this->objectVariables) { // if object has property
            foreach ($this->objectVariables as $variableName => $variableConfig) {
                if ($variableConfig->group && !$this->options->groupEnable($variableConfig->group)) { // no group or variable in selected group
                    continue;
                }

                $objectValues[$variableName] = $this->getObjectValue($variableName, $variableConfig);
            }
        }

        return $objectValues;
    }

    /**
     * Converts serialized data to target object;
     *
     * @param $serializedData
     * @param string $objectClass
     * @throws ClassNotFoundException|\ReflectionException
     * @return mixed
     */
    public function deserialize($serializedData, string $objectClass, bool $array = false)
    {
        if (!$array) {
            return $this->deserializeObject($serializedData, $objectClass);
        } else {
            $deserializedObjects = [];

            $arrayData = json_decode($serializedData, true);

            if ($arrayData) {
                foreach ($arrayData as $dataSet) {
                    $deserializedObjects[] = $this->deserializeObject($dataSet, $objectClass);
                }
            }

            return $deserializedObjects;
        }
    }

    public function deserializeObject($serializedData, string $objectClass)
    {
        if (!class_exists($objectClass)) {
            throw new ClassNotFoundException('Class '.$objectClass.' does not exists.');
        }

        if (StringHelper::isJson($serializedData) || $serializedData instanceof \stdClass || is_object($serializedData)) {
            $this->serializedData = json_decode($serializedData, true);
        } elseif (is_array($serializedData)) {
            $this->serializedData = $serializedData;
        }

        $this->object = new $objectClass();
        $this->getObjectProperties();

        if ($this->objectVariables) {
            foreach ($this->objectVariables as $objectVariableName => $objectVariableConfig) {
                $this->setObjectValue($objectVariableName, $objectVariableConfig);
            }
        }

        return $this->object;
    }

    /**
     * @param $object
     */
    private function loadObject($object): void
    {
        $this->object = $object;
    }

    /**
     * @param string $format
     */
    private function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @param array|null $options
     */
    private function loadOptions(?array $options): void
    {
        $optionsClass = new SerializerOptions();

        if ($options) {
            foreach ($options as $optionName => $optionValue) {
                if (property_exists($optionsClass, $optionName)) {
                    $optionsClass->$optionName = $optionValue;
                }
            }
        }

        $this->options = $optionsClass;
    }

    /**
     * @param $variableName
     * @param ObjectVariable $variableConfig
     * @throws \ReflectionException
     * @return mixed
     */
    private function getObjectValue($variableName, ObjectVariable $variableConfig)
    {
        $getter = 'get'.ucfirst($variableName);
        $convertedValue = null;

        if (method_exists($this->object, $getter)) {
            $rawObjectValue = $this->object->$getter();
        } elseif (property_exists($this->object, $variableName)) {
            $rawObjectValue = $this->object->$variableName;
        } else {
            throw new NoSuchPropertyException('Property '.$variableName.' of class '.get_class($this->object).' is not accessible');
        }

        if ($variableConfig->converter) { // value must be converted;
            $converterClass = new $variableConfig->converter();
            $convertedValue = $converterClass->convertFromObjectValue($rawObjectValue);
        } elseif ($variableConfig->class) { // convert class using serializer;
            $serializer = new ObjectSerializer();

            if (is_array($rawObjectValue) || is_iterable($rawObjectValue)) {
                foreach ($rawObjectValue as $rawItem) {
                    $convertedValue[] = $serializer->serialize($rawItem, $this->format);
                }
            } else {
                $convertedValue = $serializer->serialize($rawObjectValue, $this->format);
            }
        } else {
            if ((is_null($rawObjectValue) || !$rawObjectValue) && $variableConfig->nullable) {
                return null;
            }

            if (!$variableConfig->type) {
                $variableConfig->type = $this->tryDetectDataType($rawObjectValue, self::MODE_SERIALIZE);
            }

            if ($variableConfig->type) { // read type
                switch ($variableConfig->type) {
                    case ObjectVariable::TYPE_ARRAY:
                    case ObjectVariable::TYPE_ITERABLE:
                    case ObjectVariable::TYPE_STDCLASS:
                        if (!is_array($rawObjectValue)) {
                            $convertedValue = json_decode(json_encode($rawObjectValue), true);
                        }
                        break;

                    case ObjectVariable::TYPE_DATE:
                    case ObjectVariable::TYPE_DATETIME:
                        $converter = new DateTimeConverter();
                        $convertedValue = $converter->convertFromObjectValue($rawObjectValue);
                        break;

                    case ObjectVariable::TYPE_INT:
                        $convertedValue = (int)$rawObjectValue;
                        break;

                    case ObjectVariable::TYPE_FLOAT:
                        $convertedValue = (float)$rawObjectValue;
                        break;

                    case ObjectVariable::TYPE_STRING:
                        $convertedValue = (string)$rawObjectValue;
                        break;

                    case ObjectVariable::TYPE_BOOLEAN:
                        $convertedValue = ($rawObjectValue === true) ? 'true' : 'false';
                }
            } else { // no action
                $convertedValue = $rawObjectValue;
            }
        }
        
        return $convertedValue;
    }

    /**
     * @param string $variableName
     * @param ObjectVariable $variableConfig
     * @return mixed
     */
    private function setObjectValue(string $variableName, ObjectVariable $variableConfig): void
    {
        $setter = 'set'.ucfirst($variableName);
        $getter = 'get'.ucfirst($variableName);
        $isser = 'is'.ucfirst($variableName);
        $adder = 'add'.ucfirst($variableName);

        if (array_key_exists($variableName, $this->serializedData) && $this->serializedData[$variableName]) {
            $rawObjectValue = $this->serializedData[$variableName];

            if ($variableConfig->converter) { // value must be converted
                $converterClass = new $variableConfig->converter();
                $convertedValue = $converterClass->convertToObjectValue($rawObjectValue);
            } elseif ($variableConfig->class) { // value is an class
                $serializer = new ObjectSerializer();

                if (StringHelper::isJson($rawObjectValue)) {
                    $rawObjectValue = json_decode($rawObjectValue, true);
                }

                if (is_array($rawObjectValue) && (empty($rawObjectValue) || array_key_first($rawObjectValue) === 0)) {
                    if ($rawObjectValue) {
                        foreach ($rawObjectValue as $rawItem) {
                            $convertedValue[] = $serializer->deserialize($rawItem, $variableConfig->class);
                        }
                    } else {
                        $convertedValue = [];
                    }
                } else {
                    $convertedValue = $serializer->deserialize($rawObjectValue, $variableConfig->class);
                }
            } else {
                if (!$variableConfig->type) {
                    $variableConfig->type = $this->tryDetectDataType($rawObjectValue, self::MODE_DESERIALIZE);
                }

                if ($variableConfig->type) {
                    switch ($variableConfig->type) {
                        case ObjectVariable::TYPE_ARRAY:
                        case ObjectVariable::TYPE_ITERABLE:
                            $convertedValue = json_decode($rawObjectValue, true);
                            break;

                        case ObjectVariable::TYPE_STDCLASS:
                            $convertedValue = json_decode($rawObjectValue);
                            break;

                        case ObjectVariable::TYPE_DATE:
                        case ObjectVariable::TYPE_DATETIME:
                            $converter = new DateTimeConverter();
                            $convertedValue = $converter->convertToObjectValue($rawObjectValue);
                            break;

                        case ObjectVariable::TYPE_INT:
                            $convertedValue = (int)$rawObjectValue;
                            break;

                        case ObjectVariable::TYPE_FLOAT:
                            $convertedValue = (float)$rawObjectValue;
                            break;

                        case ObjectVariable::TYPE_STRING:
                            $convertedValue = (string)$rawObjectValue;
                            break;

                        case ObjectVariable::TYPE_BOOLEAN:
                            $convertedValue = ($rawObjectValue === true || mb_strtolower($rawObjectValue) === 'true');
                    }
                } else {
                    $convertedValue = $rawObjectValue;
                }
            }
        } else {
            if (!$variableConfig->nullable) {
                throw new \Exception('Variable ' . $variableName . ' can\'t be null!');
            }

            $convertedValue = null;
        }

        if ($convertedValue) {
            if (
                ($variableConfig->type === ObjectVariable::TYPE_ITERABLE && method_exists($this->object, $adder)) ||
                method_exists($this->object, $setter)
            ) { // setter available
                $this->object->$setter($convertedValue);
            } elseif (property_exists($this->object, $variableName)) { // public method available
                $this->object->$variableName = $convertedValue;
            } else {
                throw new NoSuchPropertyException('Property '.$variableName.' of class '.get_class($this->object).' is not accessible');
            }
        }
    }

    /**
     * @param array $annotationsFilter
     * @return void
     * @throws \ReflectionException
     */
    private function getObjectProperties(): void
    {
        $this->objectVariables = [];
        $objectMethods = get_class_methods($this->object);

        if ($objectMethods) {
            $reflectionClass = new \ReflectionClass(get_class($this->object));

            foreach ($objectMethods as $methodKey => $methodName) {
                if (substr($methodName, 0, 3) === 'get') {
                    $variableName = lcfirst(substr($methodName, 3));

                    if ($reflectionClass->hasProperty($variableName)) {
                        $variableProperty = $reflectionClass->getProperty($variableName);
                        $variableAnnotations = $this->annotationReader->getPropertyAnnotation(
                            $variableProperty,
                            ObjectVariable::class
                        );

                        if (is_object($variableAnnotations)) {
                            $annotationVariableArray = json_decode(json_encode($variableAnnotations));
                            $variableClass = new ObjectVariable();

                            foreach ($annotationVariableArray as $annotationVariableName => $annotationVariableValue) {
                                if (property_exists($variableClass, $annotationVariableName)) {
                                    $variableClass->$annotationVariableName = $annotationVariableValue;
                                }
                            }

                            $this->objectVariables[$variableName] = $variableClass;
                        }
                    }
                }
            }
        }
    }
    
    private function getSerializedData(string $format = self::FORMAT_ARRAY)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return json_encode($this->serializedData);

            case self::FORMAT_ARRAY:
                return $this->serializedData;
        }

        return $this->serializedData;
    }

    private function tryDetectDataType($data, string $mode): ?string
    {
        if (StringHelper::isJson($data)) {
            return $mode === self::MODE_SERIALIZE ? ObjectVariable::TYPE_JSON : ObjectVariable::TYPE_ARRAY;
        } elseif (is_object($data) && get_class($data) === \stdClass::class) {
            return ObjectVariable::TYPE_JSON;
        } elseif (is_array($data)) {
            return self::FORMAT_ARRAY;
        } elseif (is_iterable($data)) {
            return ObjectVariable::TYPE_ITERABLE;
        } elseif (is_string($data)) {
            return ObjectVariable::TYPE_STRING;
        }

        return null;
    }
}