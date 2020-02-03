<?php

namespace App\Traits;

use App\Annotation\ModelVariable;
use App\Enum\StatusCode;
use App\Model\ParserRequest;
use App\Model\Status;
use App\Utils\CacheHelper;
use Ratchet\ConnectionInterface;

trait PageLoaderTrait
{
    /**
     * @var Status
     * @ModelVariable(type="array")
     */
    public $status;

    /** @var ConnectionInterface */
    protected $websocketConnection;

    protected $statusData = [];

    /** @var \Predis\ClientInterface|\Redis|\RedisCluster */
    protected $redis;


}