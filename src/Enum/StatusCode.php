<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class StatusCode extends Enum
{
    const NoEffect = 100;

    const OperationStarted = 200;
    const OperationInProgress = 201;
    const OperationEnded = 202;

    const DuplicatedOperation = 400;
}
