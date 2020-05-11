<?php

namespace App\Tests\Functional\Basic;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BasicWebTestCase extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }
}