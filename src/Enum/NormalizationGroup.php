<?php

namespace App\Enum;

use App\Enum\Base\Enum;

class NormalizationGroup extends Enum
{
    const UserData = 'user_data';
    const QueuedFile = 'queued_file';
    const BasicData = 'basic_data';
}
