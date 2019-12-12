<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Enum\PaginationMode;

class ParserRequestModel extends AbstractModel
{
    /**
     * @ModelVariable()
     */
    public $actionName;

    /**
     * @ModelVariable(type="stdClass")
     */
    public $currentNode;

    /**
     * @ModelVariable(type="stdClass")
     */
    public $actionNode;

    /**
     * @ModelVariable(type="array")
     */
    public $parsedNodes;

    /**
     * @ModelVariable()
     */
    public $jumpPrevious = false;

    /**
     * @ModelVariable()
     */
    public $jumpNext = false;

    /**
     * @ModelVariable(type="array")
     */
    public $files;

    /**
     * @ModelVariable(type="stdClass")
     */
    public $fileData;

    /**
     * Parser name (string)
     * @ModelVariable()
     */
    public $parser;

    /**
     * @ModelVariable()
     */
    public $level;

    /**
     * @var Pagination
     * @ModelVariable(converter="Model", type="array", converterOptions={"class":"App\Model\Pagination"})
     */
    public $pagination;

    /**
     * @ModelVariable(type="stdClass")
     */
    public $tokens;

    /**
     * @ModelVariable(type="boolean")
     */
    public $ignoreCache = false;

    /**
     * @ModelVariable()
     */
    public $cachedData = false;

    /**
     * @ModelVariable(type="array")
     */
    public $sorting = [];

    public function __construct()
    {
        $this->clearData();
    }

    public function clearData()
    {
        $this->data = new \stdClass();
        $this->tokens = new \stdClass();
        $this->pagination = new \stdClass();
        $this->letteration = new \stdClass();
        $this->parsedNodes = [];
        $this->files = [];

        $this->tokens->before = null;
        $this->tokens->after = null;

        $this->pagination = new Pagination();
    }
}