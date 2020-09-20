<?php

namespace App\Enum;

use App\Enum\Base\Enum;

class RedisKey extends Enum
{
    const PreparedQueuedFilesListIds = 'prepared_queue_files_list_ids';
    const PreparedQueuedFilesListCount = 'prepared_queue_files_list_count';
    const TotalQueuedFilesCount = 'total_queued_files_count';
}
