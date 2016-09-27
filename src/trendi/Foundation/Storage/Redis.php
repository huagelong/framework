<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trendi\Foundation\Storage;

use Config;
use Trendi\Foundation\Exception\ConfigNotFoundException;
use Trendi\Pool\PoolClient;

class Redis
{
    protected $client = null;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        $config = Config::get("client.pool");
        if (!$config) {
            throw new ConfigNotFoundException("client.pool not config");
        }

        $this->client = new PoolClient($config['host'], $config['port'], $config['serialization']);
    }

    public function __call($name, $arguments)
    {
        $params = [
            $name,
            $arguments
        ];
        $data = $this->client->get("redis", $params);
        return $data;
    }

    public function __destruct()
    {
        $this->client->close();
    }
}