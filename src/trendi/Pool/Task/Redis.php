<?php
/**
 * redis pool 连接 redis服务器
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 上午11:30
 */

namespace Trendi\Pool\Task;

use Predis\Client;
use Trendi\Coroutine\SystemCall;

class Redis
{
    private static $config = [];


    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * 执行
     * 
     * @return mixed
     * @throws \Exception
     */
    public function perform()
    {
        $params = func_get_args();

        if (!isset($params[0])) {
            throw new \Exception("Invalid argument!");
        }
        $cmd = $params[0];
        $servers = self::$config['servers'];
        $options = self::$config['options'];

        try {
            $client = new Client($servers, $options);
            if (isset($params[1])) {
                return $client->$cmd(...$params[1]);
            } else {
                return $client->$cmd();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}