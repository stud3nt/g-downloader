<?php

namespace App\Traits;

use App\Annotation\ModelVariable;
use App\Model\Status;
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