<?php

namespace App\Model;

use App\Annotation\ModelVariable;

class PaginationSelector extends AbstractModel
{
    /**
     * @var string
     * @ModelVariable()
     */
    public $label = 'A';

    /**
     * @var string
     * @ModelVariable()
     */
    public $value = '';


    public $childrens = [];
}