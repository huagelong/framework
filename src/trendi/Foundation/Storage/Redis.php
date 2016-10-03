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
use Trendi\Support\Log;

class Redis
{
    protected static $client = null;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        if(self::$client) return ;
        Log::sysinfo("new redis client conn");
        $config = Config::get("client.pool");
        if (!$config) {
            throw new ConfigNotFoundException("client.pool not config");
        }

        self::$client = new PoolClient($config['host'], $config['port'], $config['serialization'], $config);
    }

    public function __call($name, $arguments)
    {
        $params = [
            $name,
            $arguments
        ];
        $data = self::$client->get("redis", $params);
        return $data;
    }

    public function __destruct()
    {
//        $this->client->close();
    }
}