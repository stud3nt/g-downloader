<?php

namespace App\Model;

use App\Annotation\ModelVariable;

class SettingsModel extends AbstractModel
{
    /**
     * @ModelVariable(type="array")
     */
    public $system = [];

    /**
     * @ModelVariable(type="array")
     */
    public $common = [];

    /**
     * @ModelVariable(type="array")
     */
    public $parsers = [];

    public function setParserSetting($parser, $name, $value): self
    {
        $this->parsers[$parser][$name] = $value;

        return $this;
    }

    public function getParserSetting($parser, $name)
    {
        return (array_key_exists($parser, $this->parsers) && array_key_exists($name, $this->parsers[$parser]))
            ? $this->parsers[$parser][$name]
            : null;
    }

    /**
     * @return mixed
     */
    public function getCommonSetting($name)
    {
        return $this->common[$name] ?? null;
    }

    /**
     * @param mixed $common
     */
    public function setCommonSetting($name, $value): void
    {
        $this->common[$name] = $value;
    }


}