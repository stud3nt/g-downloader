<?php

namespace App\Model\Interfaces;

use App\Model\Status;

interface StatusInterface
{
    public function setStatus(Status $status);

    public function getStatus(): Status;
}