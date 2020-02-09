<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class WebsocketOperation extends Enum
{
    const ParserProgress = 'parser_progress';
    const DownloadListStatus = 'download_list_status';
    const DownloadFileStatus = 'download_file_status';
}
