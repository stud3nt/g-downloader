<?php

namespace App\Converter;

use App\Annotation\ModelVariable;
use App\Model\AbstractModel;
use App\Utils\StringHelper;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ModelConverter
{
    /** @var AnnotationReader */
    protected $annotationReader;

    /** @var EntityConverter */
    protected $entityConverter;

    private $model;

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
        $this->entityConverter = new EntityConverter();
    }

    protected function loadModel(AbstractModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param AbstractModel $model
     * @return array
     * @throws \ReflectionException
     */
    public function convert(AbstractModel $model) : array
    {
        if (is_array($model)) {
            $modelArray = [];

            foreach ($model as $key => $modelRow) {
                $modelArray[$key] = $this->convert($modelRow);
            }

            return $modelArray;
        }

        return $this->convertModelToArray($model);
    }

    /**
     * @param $data
     * @param string $modelClass
     * @return mixed
     * @throws \ReflectionException
     */
    public function setData($data, &$model, bool $skipEmptyFields = false)
    {
        if ($data) {
            $this->model = $model;

            $modelVariables = $this->getModelVariables();

            if ($modelVariables) {
                foreach ($modelVariables as $variableName => $variableConfig) {
                    $variableValue = array_key_exists($variableName, $data) ? $data[$variableName] : null;

                    if (!$variableValue) {
                        if ($skipEmptyFields) {
                            continue;
                        }

                        switch ($variableConfig->type) {
                            case 'array':
                            case 'entitiesArray':
                                $variableValue = [];
                                break;

                            case 'stdClass':
                                $variableValue = new \stdClass();
                                break;

                            case 'boolean':
                                $variableValue = false;
                                break;
                        }
                    }

                    $this->setModelVariableValue($variableName, $variableConfig, $variableValue, $skipEmptyFields);
                }
            }
        }

        return $this->model;
    }

    /**
     * @param AbstractModel $model
     * @return array
     * @throws \ReflectionException
     */
    protected function convertModelToArray(AbstractModel $model) : array
    {
        $this->loadModel($model);

        $convertVariables = $this->getModelVariables();
        $variablesArray = [];

        if ($convertVariables) {
            foreach ($convertVariables as $variableName => $variableConfig) {
                $value = $this->getModelVariableValue($variableName, $variableConfig);

                if ($value instanceof \stdClass) { // TO_THINK_ABOUT: deep custom values converting???
                    $value = json_decode(json_encode($value), true);
                }

                $variablesArray[$variableName] = $value;
            }
        }

        return $variablesArray;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function getModelVariables() : array
    {
        $convertVariablesArray = [];
        $modelVariables = get_class_vars(get_class($this->model));

        if ($modelVariables) {
            $reflectionClass = new \ReflectionClass(get_class($this->model));

            foreach ($modelVariables as $variableName => $variableValue) {
                $variableProperty = $reflectionClass->getProperty($variableName);
                $variableAnnotations = $this->annotationReader->getPropertyAnnotation(
                    $variableProperty,
                    ModelVariable::class
                );

                if (is_object($variableAnnotations)) {
                    $convertVariablesArray[$variableName] = json_decode(json_encode($variableAnnotations));
                }
            }
        }

        return $convertVariablesArray;
    }

    protected function getModelVariableValue(string $variableName, \stdClass $variableConfig)
    {
        if (!property_exists($this->model, $variableName)) { // property does not exists in this model
            throw new MethodNotImplementedException(
                'Model variable: "'.$variableName.'" does not exists in class '.get_class($this->model)
            );
        }

        $value = $this->model->$variableName;

        /** @var BaseConverter $variableConverter */
        if ($value && $variableConfig->converter) { // convert model to array (if variable is a model);
            $variableConverterClass = 'App\\Converter\\'.$variableConfig->converter.'Converter';
            $variableConverter = new $variableConverterClass($variableConfig->converterOptions);

            if ($variableConfig->type === 'array') {
                $arrayValue = [];

                foreach ($value as $row) {
                    $arrayValue[] = $variableConverter->convert($row);
                }

                $value = $arrayValue;
            } else {
                $value = $variableConverter->convert($value);
            }
        }

        return $value;
    }

    protected function setModelVariableValue(string $variableName, \stdClass $variableConfig, $value = null, bool $skipEmptyFields = false)
    {
        if (!property_exists($this->model, $variableName)) { // property does not exists in this model
            throw new MethodNotImplementedException(
                'Model variable: "'.$variableName.'" does not exists in class '.get_class($this->model)
            );
        }

        if ($variableConfig->converter) { // convert data to model (if variable is a model);
            $variableConverterClass = 'App\\Converter\\'.$variableConfig->converter.'Converter';
            $variableConverter = new $variableConverterClass($variableConfig->converterOptions);

            if (!is_array($value)) {
                $value = json_decode($value, true);
            }

            if ($variableConfig->type === 'array') {
                $value = [];

                foreach ($value as $valueRow) {
                    $object = new $variableConfig->converterOptions->class();
                    $value[] = ($valueRow instanceof $variableConfig->converterOptions->class)
                        ? $valueRow
                        : $variableConverter->setData($valueRow, $object, $skipEmptyFields);
                }
            } else {
                $object = new $variableConfig->converterOptions->class();
                $value = ($value instanceof $variableConfig->converterOptions->class)
                    ? $value
                    : $variableConverter->setData($value, $object, $skipEmptyFields);
            }
        } else {
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

        $this->model->$variableName = $value;
    }
}