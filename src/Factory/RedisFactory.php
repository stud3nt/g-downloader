<?php

namespace App\Factory;

use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisFactory
{
    /**
     * @return \Predis\ClientInterface|\Redis|\RedisCluster
     */
    public function initializeConnection()
    {
        return RedisAdapter::createConnection('redis://127.0.0.1:6379', [
            'compression' => true,
            'lazy' => false,
            'persistent' => 0,
            'persistent_id' => null,
            'tcp_keepalive' => 0,
            'timeout' => 30,
            'read_timeout' => 0,
            'retry_interval' => 0,
        ]);
    }
}